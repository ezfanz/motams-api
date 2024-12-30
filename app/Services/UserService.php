<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use Carbon\Carbon;
use App\Models\OfficeLeaveRequest;
use App\Models\Calendar;
use App\Models\AttendanceRecord;
use App\Models\ColourChange;
use App\Models\ReasonTransaction;
use App\Models\TransAlasan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\PenukaranWarna;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUserWithRole(array $data): array
    {
        $user = $this->userRepository->create($data);
        $role = $this->userRepository->findRoleById($data['role_id']);
        if ($role) {
            $user->assignRole($role->name);
        }

        return ApiResponseHelper::formatUserResponse($user);
    }

   /**
 * Service logic for logging in a user.
 *
 * @param array $credentials
 * @return array|null
 */
public function loginUser(array $credentials): ?array
{
    try {
        // Find the user while bypassing global scopes to avoid interference
        $user = User::where('is_deleted', '!=', 1)
            ->where('email', $credentials['email'])
            ->first();

        // Verify the user exists
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Verify the password
        if (!Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid password');
        }

        Log::info('Auth::attempt failed', ['credentials' => $credentials]);
        // Generate the JWT token using credentials
        if (!$token = Auth::attempt($credentials)) {
            throw new \Exception('Invalid email or password');
        }

     
        // Retrieve the authenticated user
        $user = Auth::user();

        // Optional: Update the user's last login timestamp
        // $user->last_login_at = now();
        // $user->save();

        // Return the formatted user data and token
        return [
            'user' => ApiResponseHelper::formatUserResponse($user),
            'token' => $this->formatTokenResponse($token),
        ];
    } catch (\Exception $e) {
        // Log the error for debugging and return null
        Log::error('Login error: ' . $e->getMessage());
        return null;
    }
}


    protected function formatTokenResponse($token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ];
    }

    public function getAllUsers()
    {
        // Fetch all users and eager load relationships
        $users = $this->userRepository->all()->load(['reviewingOfficer', 'approvingOfficer', 'roles']);

        // Format each user's response
        return $users->map(function ($user) {
            return ApiResponseHelper::formatUserResponse($user);
        });
    }


    public function getUserById($id)
    {
        try {
            // Fetch the user and eager load necessary relationships
            $user = $this->userRepository->find($id)->load(['reviewingOfficer', 'approvingOfficer', 'roles']);

            // Format the user data using ApiResponseHelper
            return ApiResponseHelper::formatUserResponse($user);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('User not found.');
        }
    }

    public function updateUser($id, array $data)
    {
        try {
            $user = $this->userRepository->update($id, $data);
            if (isset($data['role_id'])) {
                $role = $this->userRepository->findRoleById($data['role_id']);
                if ($role) {
                    $user->syncRoles([$role->name]);
                }
            }
            return ApiResponseHelper::formatUserResponse($user);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('User not found for update.');
        }
    }

    public function deleteUser($id)
    {
        try {
            return $this->userRepository->delete($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('User not found for deletion.');
        }
    }

    public function getUserProfile($userId)
    {
        $user = $this->userRepository->find($userId);

        // Calculate remaining hours (baki tempoh keluar pejabat)
        $remainingHours = $this->calculateRemainingHours($userId);

        // Get attendance summary (late_in, early_out, absent)
        $attendanceSummary = $this->getAttendanceSummary($userId);

        // Get color change count
        $countColorsAll = PenukaranWarna::leftJoin('warna', 'warna.id', '=', 'penukaranwarna.warna')
            ->where('penukaranwarna.is_deleted', '!=', 1)
            ->where('penukaranwarna.idpeg', $userId)
            ->whereIn('penukaranwarna.status', [7, 8, 9, 10, 11, 12])
            ->count();

        // Get the user's role
        $role = $user->role_id ? Role::find($user->role_id)->diskripsi : 'No Role';

        // Calculate tindakan_kelulusan_count
        $tindakan_kelulusan_count = OfficeLeaveRequest::where('status', '15')
            ->where('pelulus_id', $userId)
            ->count();

        // Calculate bilsemakan and bilpengesahan
        [$bilsemakan, $bilpengesahan] = $this->calculateBilCounts($userId, $role);

        return [
            'name' => $user->fullname,
            'email' => $user->email,
            'role' => $role,
            'last_login' => $user->last_login_at,
            'remaining_hours' => $remainingHours,
            'attendance_summary' => $attendanceSummary,
            'color_change_count' => $countColorsAll,
            'total_leave_requests' => $tindakan_kelulusan_count,
            'total_pending_reviews' => $bilsemakan,
            'total_pending_approvals' => $bilpengesahan,
        ];
    }

    private function calculateRemainingHours($userId)
    {
        $remainingHours = OfficeLeaveRequest::where('idpeg', $userId)
            ->whereDate('date_mula', now()->toDateString())
            ->where('status', 'Approved')
            ->where('is_deleted', '!=', 1)
            ->selectRaw('(4 - SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60)) AS remaining_hours')
            ->value('remaining_hours');

        if ($remainingHours !== null) {
            $hours = floor($remainingHours);
            $minutes = ($remainingHours - $hours) * 60;

            return sprintf('%02d:%02d', $hours, $minutes);
        }

        return '04:00';
    }

    public function getAttendanceSummary($userId)
    {
        $startOfPreviousMonth = now()->subMonths(3)->startOfMonth();
        $endDate = now();

        $calendarRecords = Calendar::select(
            'calendars.fulldate',
            'calendars.year',
            'calendars.monthname',
            'calendars.dayname',
            'calendars.isweekday',
            'calendars.isholiday',
            'calendars.holidaydesc',
            'transit.staffid',
            'transit.ramadhan_yt',
            DB::raw("MIN(transit.trdatetime) AS datetimein"),
            DB::raw("DATE_FORMAT(MIN(transit.trdatetime), '%T') AS timein"),
            DB::raw("MAX(transit.trdatetime) AS datetimeout"),
            DB::raw("DATE_FORMAT(MAX(transit.trdatetime), '%T') AS timeout"),
            DB::raw("
            CASE
                WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND TIME(MIN(transit.trdatetime)) >= '09:01:00' THEN 1
                ELSE 0
            END AS latein
        "),
            DB::raw("
            CASE
                WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND transit.ramadhan_yt = 0 AND TIME(MAX(transit.trdatetime)) <= '18:00:00'
                    AND (HOUR(TIMESTAMPADD(MINUTE, 540, MIN(transit.trdatetime))) * 60 + MINUTE(TIMESTAMPADD(MINUTE, 540, MIN(transit.trdatetime)))) > (HOUR(MAX(transit.trdatetime)) * 60 + MINUTE(MAX(transit.trdatetime)))
                    THEN 1
                WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND transit.ramadhan_yt = 0 AND (TIME(MIN(transit.trdatetime)) = TIME(MAX(transit.trdatetime)) OR TIME(MAX(transit.trdatetime)) <= '16:30:00') THEN 1
                WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND transit.ramadhan_yt = 1 AND TIME(MAX(transit.trdatetime)) <= '18:00:00'
                    AND (HOUR(TIMESTAMPADD(MINUTE, 510, MIN(transit.trdatetime))) * 60 + MINUTE(TIMESTAMPADD(MINUTE, 510, MIN(transit.trdatetime)))) > (HOUR(MAX(transit.trdatetime)) * 60 + MINUTE(MAX(transit.trdatetime)))
                    THEN 1
                WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND transit.ramadhan_yt = 1 AND (TIME(MIN(transit.trdatetime)) = TIME(MAX(transit.trdatetime)) OR TIME(MAX(transit.trdatetime)) <= '16:00:00') THEN 1
                ELSE 0
            END AS earlyout
        ")
        )
            ->leftJoin('transit', function ($join) use ($userId) {
                $join->on(DB::raw('calendars.fulldate'), '=', DB::raw('DATE(transit.trdate)'))
                    ->where('transit.staffid', $userId);
            })
            ->where('calendars.fulldate', '>=', $startOfPreviousMonth->format('Y-m-d'))
            ->where('calendars.fulldate', '<=', $endDate)
            ->groupBy(
                'calendars.fulldate',
                'calendars.year',
                'calendars.monthname',
                'calendars.dayname',
                'calendars.isweekday',
                'calendars.isholiday',
                'calendars.holidaydesc',
                'transit.staffid',
                'transit.ramadhan_yt'
            )
            ->get();

        $absentCount = 0;
        $lateCount = 0;
        $earlyOutCount = 0;

        foreach ($calendarRecords as $record) {
            if ($record->isweekday && !$record->isholiday) {
                if (is_null($record->datetimein) && is_null($record->datetimeout)) {
                    $absentCount++;
                }
                if ($record->latein == 1) {
                    $lateCount++;
                }
                if ($record->earlyout == 1) {
                    $earlyOutCount++;
                }
            }
        }

        return [
            'lewat_tanpa_sebab' => $lateCount,
            'balik_awal_tanpa_sebab' => $earlyOutCount,
            'tidak_hadir_tanpa_sebab' => $absentCount,
        ];
    }

    private function calculateBilCounts($userId, $role)
    {
        $currentDay = Carbon::now()->format('d');
        $currentMonth = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
    
        $bilsemakan = 0;
        $bilpengesahan = 0;
    
        // Define role categories for semakan and pengesahan
        $semakanRoles = [5, 7, 8, 10, 11, 13, 15, 17]; // Penyemak roles
        $pengesahanRoles = [6, 7, 9, 10, 12, 13, 16, 17]; // Pengesah roles
    
        if ($role) {
            if (in_array($role, [3, 2])) { // Admin or Pentadbir
                if ($currentDay > 10) {
                    $bilsemakan = TransAlasan::where('is_deleted', '!=', 1)
                        ->where('status', 1)
                        ->whereMonth('log_datetime', now()->month)
                        ->count();
    
                    $bilpengesahan = TransAlasan::where('is_deleted', '!=', 1)
                        ->where('status', 2)
                        ->whereMonth('log_datetime', now()->month)
                        ->count();
                } else {
                    $bilsemakan = TransAlasan::where('is_deleted', '!=', 1)
                        ->where('status', 1)
                        ->where(function ($query) use ($currentMonth, $lastMonth) {
                            $query->whereMonth('log_datetime', now()->month)
                                ->orWhereMonth('log_datetime', now()->subMonth()->month);
                        })
                        ->count();
    
                    $bilpengesahan = TransAlasan::where('is_deleted', '!=', 1)
                        ->where('status', 2)
                        ->where(function ($query) use ($currentMonth, $lastMonth) {
                            $query->whereMonth('log_datetime', now()->month)
                                ->orWhereMonth('log_datetime', now()->subMonth()->month);
                        })
                        ->count();
                }
            }
    
            if (in_array($role, $semakanRoles)) { // Penyemak roles
                $bilsemakan = TransAlasan::where('status', 1)
                    ->where('is_deleted', '!=', 1)
                    ->when($currentDay > 10, function ($query) {
                        return $query->whereMonth('log_datetime', now()->month);
                    }, function ($query) use ($currentMonth, $lastMonth) {
                        return $query->where(function ($q) use ($currentMonth, $lastMonth) {
                            $q->whereMonth('log_datetime', now()->month)
                                ->orWhereMonth('log_datetime', now()->subMonth()->month);
                        });
                    })
                    ->count();
            }
    
            if (in_array($role, $pengesahanRoles)) { // Pengesah roles
                $bilpengesahan = TransAlasan::where('status', 2)
                    ->where('is_deleted', '!=', 1)
                    ->when($currentDay > 10, function ($query) {
                        return $query->whereMonth('log_datetime', now()->month);
                    }, function ($query) use ($currentMonth, $lastMonth) {
                        return $query->where(function ($q) use ($currentMonth, $lastMonth) {
                            $q->whereMonth('log_datetime', now()->month)
                                ->orWhereMonth('log_datetime', now()->subMonth()->month);
                        });
                    })
                    ->count();
            }
        }
    
        return [$bilsemakan, $bilpengesahan];
    }
    

}

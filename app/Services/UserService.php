<?php

namespace App\Services;

use App\Services\AttendanceRecordService;
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
use GuzzleHttp\Client;
use App\Models\Department;
use App\Models\ActiveDepartment;
use App\Helpers\TitleHelper;
use App\Models\Transit;
use App\Models\Status;



class UserService
{
    protected $userRepository;
    protected $attendanceRecordService;
    public function __construct(UserRepository $userRepository, AttendanceRecordService $attendanceRecordService)
    {
        $this->userRepository = $userRepository;
        $this->attendanceRecordService = $attendanceRecordService;
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
     * @param object|null $ADUser
     * @return array|null
     */
    public function loginUser(array $credentials, ?object $ADUser = null): ?array
    {
        try {
            // Find the user by username while bypassing global scopes
            $user = User::where('is_deleted', '!=', 1)
                ->where('username', $credentials['username']) // Changed to username
                ->first();

            // Verify the user exists
            if (!$user) {
                throw new \Exception('User not found in the local database');
            }

            // Verify the username matches the AD response if provided
            if ($ADUser && $ADUser->username !== $user->username) {
                throw new \Exception('Active Directory username mismatch');
            }

            // Generate the JWT token using only the username
            $token = Auth::login($user);

            // Retrieve the authenticated user
            $authenticatedUser = Auth::user();

            // Return the formatted user data and token
            return [
                'user' => ApiResponseHelper::formatUserResponse($authenticatedUser),
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
        $users = $this->userRepository->all()->load(['penyemak', 'pengesah', 'roles']);

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
        $user = User::where('is_deleted', '!=', 1)->find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $idpeg = $user->id;
        $idstaff = $user->staffid;
        $nama = $user->fullname;
        $jawatan = $user->jawatan;
        $roleId = $user->role_id;
        $logterakhir = $user->updated_at;

        // Fetch latest trans_alasan for the user
        $transAlasan = TransAlasan::where('idpeg', $idpeg)
            ->where('is_deleted', 0)
            ->orderBy('log_datetime', 'DESC') // Get the latest transaction
            ->first();

        $transAlasanDetails = $transAlasan ? [
            'transid' => $transAlasan->id,
            'log_datetime' => $transAlasan->log_datetime,
            'alasan_id' => $transAlasan->alasan_id,
            'jenisalasan_id' => $transAlasan->jenisalasan_id,
            'catatan_peg' => $transAlasan->catatan_peg,
            'status' => $transAlasan->status,
        ] : null;

        $totalPendingReviews = $this->attendanceRecordService->getReviewCount($idpeg, $roleId);

        // Enable Query Logging
        DB::enableQueryLog();

        // Check if office leave request exists for today
        $leaveExists = DB::table('office_leave_requests')
            ->where('idpeg', $idpeg)
            ->whereDate('date_mula', now()->toDateString())
            ->where('status', '16')
            ->where('is_deleted', 0)
            ->exists();

        if (!$leaveExists) {
            $remainingHours = '--:--:--';
        } else {
            // Use raw query to get remaining hours
            $result = DB::select("
            SELECT (4 - IFNULL(SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60), 0)) AS remaining_hours
            FROM office_leave_requests
            WHERE idpeg = ? 
            AND date_mula = ? 
            AND status = ? 
            AND is_deleted = ?
        ", [$idpeg, now()->toDateString(), '16', 0]);

            // Extract value from query result
            $remainingHours = $result[0]->remaining_hours ?? null;

            \Log::info("Raw remaining_hours from DB: " . json_encode($remainingHours));

            if ($remainingHours !== null) {
                $hours = floor($remainingHours);
                $minutes = floor(($remainingHours - $hours) * 60);

                if ($minutes >= 60) {
                    $hours++;
                    $minutes = 0;
                }

                $remainingHours = sprintf('%02d:%02d', $hours, $minutes);
            } else {
                $remainingHours = '04:00';
            }
        }

        \Log::info("Formatted Remaining Hours: " . json_encode($remainingHours));

        // Get today's attendance log
        $datenow = now()->format('Y-m-d');
        $attendanceLog = Transit::select(
            DB::raw('TIME_FORMAT(MIN(trdatetime), "%T") AS timein'),
            DB::raw('TIME_FORMAT(MAX(trdatetime), "%T") AS timeout')
        )
            ->where('staffid', $idstaff)
            ->where('trdate', $datenow)
            ->first();

        $timein = $attendanceLog->timein ?? '--:--:--';
        $timeout = $attendanceLog->timeout ?? '--:--:--';

        // Attendance summary
        $attendanceSummary = $this->getAttendanceSummary($idstaff, $idpeg);

        // Color change count
        $countColorsAll = PenukaranWarna::leftJoin('warna', 'warna.id', '=', 'penukaranwarna.warna')
            ->where('penukaranwarna.is_deleted', '!=', 1)
            ->where('penukaranwarna.idpeg', $idpeg)
            ->whereIn('penukaranwarna.status', [7, 8, 9, 10, 11, 12])
            ->count();

        // Total leave requests
        $tindakan_kelulusan_count = OfficeLeaveRequest::where('status', '15')
            ->where('pelulus_id', $idpeg)
            ->count();

        // Pending reviews and approvals
        [$bilsemakan, $bilpengesahan] = $this->calculateBilCounts($userId, $roleId);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile retrieved successfully',
            'data' => [
                'name' => $nama,
                'jawatan' => $jawatan,
                'logterakhir' => $logterakhir,
                'remaining_hours' => $remainingHours,
                'timein' => $timein,
                'timeout' => $timeout,
                'attendance_summary' => $attendanceSummary,
                'color_change_count' => $countColorsAll,
                'total_leave_requests' => $tindakan_kelulusan_count,
                'total_pending_reviews' => $totalPendingReviews,
                'total_pending_approvals' => $bilpengesahan,
                'trans_alasan' => $transAlasanDetails
            ]
        ]);
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

    public function getAttendanceSummary($idstaff, $idpeg)
    {
        $startDate = now()->startOfMonth()->toDateString(); // Start from beginning of the month
        $yesterday = Carbon::yesterday()->toDateString(); // Exclude today's date
    
        // Query for lateness & early out
        $calendarRecords = Calendar::select(
            'calendars.fulldate',
            'calendars.isweekday',
            'calendars.isholiday',
            DB::raw("MIN(transit.trdatetime) AS datetimein"),
            DB::raw("MAX(transit.trdatetime) AS datetimeout"),
            DB::raw("CASE 
                        WHEN calendars.isweekday = 1 
                        AND calendars.isholiday = 0 
                        AND TIME(MIN(transit.trdatetime)) >= '09:01:00' 
                        THEN 1 
                        ELSE 0 
                     END AS latein")
        )
            ->leftJoin('transit', function ($join) use ($idstaff) {
                $join->on(DB::raw('DATE(calendars.fulldate)'), '=', DB::raw('DATE(transit.trdate)'))
                    ->where('transit.staffid', $idstaff);
            })
            ->whereBetween('calendars.fulldate', [$startDate, $yesterday]) // Only fetch for this month excluding today
            ->groupBy('calendars.fulldate', 'calendars.isweekday', 'calendars.isholiday')
            ->get();

        // Fetch early out records (excluding today)
        $earlyOutCount = DB::lateinoutviewFix("
        SELECT COUNT(*) as total FROM lateinoutview 
        WHERE CAST(staffid AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_general_ci = $idstaff
        AND trdate BETWEEN '$startDate' AND '$yesterday'
        AND earlyout = 1
        AND isweekday = 1
        AND isholiday = 0
    ")[0]->total;

        // Fetch absence records count (excluding today)
        $absentRecords = $this->fetchAbsentRecordsCount($idpeg, $startDate, $yesterday);
        $absentCount = $absentRecords['total_absent_count'];
    
        // Initialize lateness counter
        $lateCount = 0;
    
        foreach ($calendarRecords as $record) {
            if ($record->isweekday && !$record->isholiday) {
                if ($record->latein == 1) {
                    $lateCount++;
                }
            }
        }
    
        return [
            'lewat_tanpa_sebab' => $lateCount, // Excluding today
            'balik_awal_tanpa_sebab' => $earlyOutCount, // Excluding today
            'tidak_hadir_tanpa_sebab' => $absentCount, // Excluding today
        ];
    }
    
    

    private function calculateBilCounts($userId, $roleId)
    {
        $currentMonthStart = now()->startOfMonth()->toDateTimeString();
        $currentMonthEnd = now()->endOfMonth()->toDateTimeString();

        // Ensure status constants are correctly referenced
        $pendingReviewStatus = Status::MENUNGGU_SEMAKAN;
        $pendingApprovalStatus = Status::DITERIMA_PENYEMAK;

        // Count for pending approvals (status = DITERIMA_PENYEMAK)
        $bilpengesahan = TransAlasan::where('status', $pendingApprovalStatus)
            ->where('is_deleted', 0)
            ->where('pengesah_id', $userId) // Ensure only approvals for this user
            ->whereBetween('log_datetime', [$currentMonthStart, $currentMonthEnd])
            ->count();

        // Count for pending reviews (status = MENUNGGU_SEMAKAN)
        $bilsemakan = TransAlasan::where('status', $pendingReviewStatus)
            ->where('is_deleted', 0)
            ->where('penyemak_id', $userId) // Ensure only reviews for this user
            ->whereBetween('log_datetime', [$currentMonthStart, $currentMonthEnd])
            ->count();

        return [$bilsemakan, $bilpengesahan];
    }

    /**
     * Fetch user profile data by user ID.
     *
     * @param int $userId
     * @return array|null
     */
    public function fetchUserProfile(int $userId): ?array
    {
        return $this->userRepository->getUserProfile($userId);
    }


    /**
     * Authenticate a user with Active Directory and retrieve their details.
     *
     * @param array $credentials
     * @return object|null
     */
    public function activeDirectoryAuthenticateAndRetrieve(array $credentials): ?object
    {
        try {
            $client = new Client();
            $url = config('app.ad_url');

            $response = $client->request('GET', $url, [
                'query' => [
                    'email' => $credentials['username'],
                    'pass' => $credentials['password'],
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // Log Active Directory response for debugging
            Log::info('Active Directory Authentication Response', ['response' => $data]);

            // Validate the required fields
            $requiredFields = ['username', 'userprincipalname'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    return null; // Missing required field
                }
            }

            // Ensure username matches
            if ($data['username'] !== $credentials['username']) {
                return null; // Username mismatch
            }

            return (object) $data; // Return AD user data
        } catch (\Exception $e) {
            Log::error('Active Directory error: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Sync Active Directory user with local database.
     *
     * @param object $ADUser
     * @return void
     */
    public function syncActiveDirectoryUser($ADUser)
    {
        $user = User::where('username', $ADUser->username)->first();
        $datenow = Carbon::now()->format('Y-m-d H:i:s');

        // Find or create the user's department
        $department = Department::firstOrCreate(
            ['diskripsi' => $ADUser->department],
            ['created_at' => $datenow]
        );

        Log::info('Department sync', [
            'department' => $department,
            'ADUser_department' => $ADUser->department,
        ]);

        if ($user) {
            // Update existing user details
            $user->update([
                'fullname' => $ADUser->displayname,
                'email' => $ADUser->userprincipalname,
                'department_id' => $department->id,
                'phone' => $ADUser->telephonenumber,
                'jawatan' => $ADUser->title,
                'gred' => TitleHelper::getGradeFromTitle($ADUser->title), // Use helper
                'kump_khidmat' => TitleHelper::getServiceGroupFromTitle($ADUser->title), // Use helper
            ]);

            Log::info('User updated', [
                'user_id' => $user->id,
                'username' => $ADUser->username,
                'updated_data' => [
                    'fullname' => $ADUser->displayname,
                    'email' => $ADUser->userprincipalname,
                    'department_id' => $department->id,
                    'phone' => $ADUser->telephonenumber,
                    'jawatan' => $ADUser->title,
                    'gred' => TitleHelper::getGradeFromTitle($ADUser->title),
                    'kump_khidmat' => TitleHelper::getServiceGroupFromTitle($ADUser->title),
                ],
            ]);
        } else {
            // Create a new user
            $user = User::create([
                'username' => $ADUser->username,
                'fullname' => $ADUser->displayname,
                'email' => $ADUser->userprincipalname,
                'department_id' => $department->id,
                'phone' => $ADUser->telephonenumber,
                'jawatan' => $ADUser->title,
                'gred' => TitleHelper::getGradeFromTitle($ADUser->title), // Use helper
                'kump_khidmat' => TitleHelper::getServiceGroupFromTitle($ADUser->title), // Use helper
            ]);

            Log::info('User created', [
                'user_id' => $user->id,
                'username' => $ADUser->username,
                'created_data' => [
                    'fullname' => $ADUser->displayname,
                    'email' => $ADUser->userprincipalname,
                    'department_id' => $department->id,
                    'phone' => $ADUser->telephonenumber,
                    'jawatan' => $ADUser->title,
                    'gred' => TitleHelper::getGradeFromTitle($ADUser->title),
                    'kump_khidmat' => TitleHelper::getServiceGroupFromTitle($ADUser->title),
                ],
            ]);

            ActiveDepartment::create([
                'idpeg' => $user->id,
                'datestarts' => $datenow,
                'department_id' => $department->id,
                'id_pencipta' => $user->id,
            ]);

            Log::info('ActiveDepartment record created', [
                'idpeg' => $user->id,
                'datestarts' => $datenow,
                'department_id' => $department->id,
                'id_pencipta' => $user->id,
            ]);
        }
    }

    public function fetchAbsentRecordsCount(int $userId, string $startDay, string $endDay): array
    {
        // Get staff ID
        $idStaff = User::where('is_deleted', '!=', 1)
            ->where('id', $userId)
            ->value('staffid');
    
        if (!$idStaff) {
            Log::error("Error: Staff ID not found for user ID: $userId");
            return ['total_absent_count' => 0];
        }
    
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
    
        // Check if there is transit data for today
        $hasTodayTransit = DB::table('transit')
            ->whereRaw('DATE(transit.trdate) = ?', [$today])
            ->where('transit.staffid', '=', $idStaff)
            ->exists();
    
        if ($hasTodayTransit) {
            // If today has transit records, exclude today from absent count
            $endDay = $yesterday;
        }
    
        // Count days where the user was absent (No transit record on working days)
        $totalAbsent = DB::table('calendars')
            ->whereBetween('calendars.fulldate', [$startDay, $endDay]) // From start of month until endDay (yesterday if today exists)
            ->where('calendars.isweekday', 1) // Only working days
            ->where('calendars.isholiday', 0) // Not a holiday
            ->whereNotExists(function ($query) use ($idStaff) {
                $query->select(DB::raw(1))
                    ->from('transit')
                    ->whereRaw('DATE(transit.trdate) = DATE(calendars.fulldate)')
                    ->where('transit.staffid', '=', $idStaff);
            }) // Ensure NO transit records exist for that day
            ->count(); // Get the count of such days
    
        return ['total_absent_count' => $totalAbsent];
    }
    

}

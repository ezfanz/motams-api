<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Models\OfficeLeaveRequest;
use App\Models\Calendar;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;

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

    public function loginUser(array $credentials): ?array
    {
        if (!$token = Auth::attempt($credentials)) {
            return null;
        }

        $user = Auth::user();

        // Update the last login timestamp
        $user->last_login_at = now();
        $user->save();

        return [
            'user' => ApiResponseHelper::formatUserResponse($user),
            'token' => $this->formatTokenResponse($token)
        ];
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

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first() ?? 'No Role',
            'last_login' => $user->last_login_at,
            'remaining_hours' => $remainingHours,
            'attendance_summary' => $attendanceSummary, // Add attendance summary
        ];
    }

    /**
     * Calculate the remaining hours for the user's office leave requests.
     *
     * @param int $userId
     * @return string|null
     */
    private function calculateRemainingHours($userId)
    {
        $remainingHours = OfficeLeaveRequest::where('created_by', $userId)
            ->whereDate('date', now()->toDateString()) // Current date
            ->where('status', 'Approved') // Ensure the leave is approved
            ->whereNull('deleted_at') // Ensure it is not soft-deleted
            ->selectRaw('(4 - SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60)) AS remaining_hours')
            ->value('remaining_hours');

        // Format remaining hours to HH:MM or return null if no remaining hours
        if ($remainingHours !== null) {
            $hours = floor($remainingHours);
            $minutes = ($remainingHours - $hours) * 60;

            return sprintf('%02d:%02d', $hours, $minutes);
        }

        return '04:00'; // Default to 4 hours if no records are found
    }

    public function getAttendanceSummary($userId)
    {
        $startOfPreviousMonth = now()->subMonths(3)->startOfMonth();
        $endDate = now();
    
        // Query calendar records and join attendance logs
        $calendarRecords = Calendar::select(
            'calendars.fulldate',
            'calendars.dayname',
            'calendars.isweekday',
            'calendars.isholiday',
            'calendars.holidaydesc',
            'calendars.is_ramadhan',
            DB::raw("MIN(attendance_logs.time_in) AS timein"),
            DB::raw("MAX(attendance_logs.time_out) AS timeout"),
            DB::raw("
                CASE
                    WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND TIME(MIN(attendance_logs.time_in)) >= '09:01:00' THEN 1
                    ELSE 0
                END AS latein
            "),
            DB::raw("
                CASE
                    WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND calendars.is_ramadhan = 0 AND TIME(MAX(attendance_logs.time_out)) <= '18:00:00'
                        AND TIMEDIFF(MAX(attendance_logs.time_out), MIN(attendance_logs.time_in)) < '09:00:00' THEN 1
                    WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND calendars.is_ramadhan = 1 AND TIME(MAX(attendance_logs.time_out)) <= '16:00:00'
                        AND TIMEDIFF(MAX(attendance_logs.time_out), MIN(attendance_logs.time_in)) < '07:00:00' THEN 1
                    ELSE 0
                END AS earlyout
            ")
        )
            ->leftJoin('attendance_logs', function ($join) use ($userId) {
                $join->on('calendars.fulldate', '=', 'attendance_logs.date')
                    ->where('attendance_logs.user_id', $userId);
            })
            ->whereBetween('calendars.fulldate', [$startOfPreviousMonth, $endDate])
            ->groupBy('calendars.fulldate', 'calendars.dayname', 'calendars.isweekday', 'calendars.isholiday', 'calendars.holidaydesc', 'calendars.is_ramadhan')
            ->get();
    
        // Fetch attendance statuses from the AttendanceRecord table
        $attendanceRecords = AttendanceRecord::where('created_by', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endDate])
            ->with('attendanceStatus')
            ->get();
    
        $statusCounts = [
            'TIDAK HADIR' => 0,
            'LEWAT' => 0,
            'BALIK AWAL' => 0,
        ];
    
        foreach ($attendanceRecords as $record) {
            $statusType = $record->attendanceStatus->status_type ?? null;
            if ($statusType && isset($statusCounts[$statusType])) {
                $statusCounts[$statusType]++;
            }
        }
    
        // Initialize counters for additional calculations
        $absentCount = $statusCounts['TIDAK HADIR'];
        $lateCount = $statusCounts['LEWAT'];
        $earlyOutCount = $statusCounts['BALIK AWAL'];
    
        // Check for days in the calendar that should be marked as "absent"
        foreach ($calendarRecords as $record) {
            $hasAttendanceRecord = $attendanceRecords->contains(function ($attendanceRecord) use ($record) {
                return $attendanceRecord->date == $record->fulldate;
            });
    
            // if ($record->isweekday && !$record->isholiday && !$hasAttendanceRecord && is_null($record->timein) && is_null($record->timeout)) {
            //     $absentCount++;
            // }
        }
    
        return [
            'lewat' => $lateCount,
            'balik_awal' => $earlyOutCount,
            'tidak_hadir' => $absentCount,
        ];
    }
    

}

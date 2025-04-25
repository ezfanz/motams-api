<?php

namespace App\Services;

use App\Repositories\AttendanceRecordRepository;
use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceRecordService
{
    protected $attendanceRecordRepository;

    public function __construct(AttendanceRecordRepository $attendanceRecordRepository)
    {
        $this->attendanceRecordRepository = $attendanceRecordRepository;
    }

    public function getAllAttendanceRecords()
    {
        return $this->attendanceRecordRepository->all();
    }

    public function getAttendanceRecordById($id)
    {
        try {
            return $this->attendanceRecordRepository->find($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Attendance record not found.');
        }
    }

    public function createAttendanceRecord(array $data)
    {
        return $this->attendanceRecordRepository->create($data);
    }

    public function updateAttendanceRecord($id, array $data)
    {
        try {
            return $this->attendanceRecordRepository->update($id, $data);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Attendance record not found for update.');
        }
    }

    public function deleteAttendanceRecord($id)
    {
        try {
            $this->attendanceRecordRepository->delete($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Attendance record not found for deletion.');
        }
    }

    public function getRecordsByStatus(int $statusId)
    {
        return $this->attendanceRecordRepository->getByStatus($statusId);
    }

    public function getAttendanceLogs($userId, $staffId)
    {
        // Log the input parameters
        Log::info("Getting attendance logs with parameters", [
            'userId' => $userId,
            'staffId' => $staffId,
        ]);

        $daynow = Carbon::now()->format('d');
        $firstDayofCurrentMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $firstDayofPreviousMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastDayofCurrentMonth = Carbon::now()->endOfMonth()->toDateTimeString();

        $startDay = $daynow > 10 ? $firstDayofCurrentMonth : $firstDayofPreviousMonth;

        // Log date range being used
        Log::info("Fetching logs between dates", [
            'startDay' => $startDay,
            'lastDay' => $lastDayofCurrentMonth,
        ]);

        return $this->attendanceRecordRepository->getAttendanceRecordsWithDetails($userId, $staffId, $startDay, $lastDayofCurrentMonth);
    }

    public function getAttendanceRecords(int $userId, string $type): array
    {
        // Calculate the date range
        $dayNow = now()->format('d');
        $firstDayOfCurrentMonth = now()->startOfMonth()->toDateTimeString();
        $firstDayOfPreviousMonth = now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastDayOfCurrentMonth = now()->endOfMonth()->toDateTimeString();

        $startDay = $dayNow > 10 ? $firstDayOfCurrentMonth : $firstDayOfPreviousMonth;

        // Fetch records based on the type
        switch ($type) {
            case 'late':
                return $this->attendanceRecordRepository->fetchLateAttendanceRecords($userId, $startDay, $lastDayOfCurrentMonth);
            case 'absent':
                return $this->attendanceRecordRepository->fetchAbsentRecords($userId, $startDay, $lastDayOfCurrentMonth);
            case 'back-early':
                return $this->attendanceRecordRepository->fetchEarlyLeaveRecords($userId, $startDay, $lastDayOfCurrentMonth);
            default:
                throw new \InvalidArgumentException('Invalid attendance record type specified.');
        }
    }

    /**
     * Get review count based on the user role and current date range.
     *
     * @param int $userId
     * @param int $role
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getReviewCount(int $userId, int $role): int
    {
        $dayNow = Carbon::now()->format('d');
        $currentMonth = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');

        if (in_array($role, [3, 2])) { // Admin and Pentadbir
            return $this->attendanceRecordRepository->getAdminReviewCount($dayNow, $currentMonth, $lastMonth);
        }

        if (in_array($role, [5, 7, 8, 10, 11, 13, 15, 17])) { // Penyemak roles
            return $this->attendanceRecordRepository->getReviewerReviewCount($userId, $dayNow, $currentMonth, $lastMonth);
        }

        throw new \InvalidArgumentException('Invalid role specified');
    }



      /**
     * Get approval count based on the user role and current date range.
     *
     * @param int $userId
     * @param int $role
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getApprovalCount(int $userId, int $role): int
    {
        $dayNow = Carbon::now()->format('d');
        $currentMonth = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');

        if (in_array($role, [3, 2])) { // Admin and Pentadbir
            return $this->attendanceRecordRepository->getAdminApprovalCount($dayNow, $currentMonth, $lastMonth);
        }

        if (in_array($role, [5, 7, 8, 10, 11, 13, 15, 17])) { // Penyemak roles
            return $this->attendanceRecordRepository->getApproverApprovalCount($userId, $dayNow, $currentMonth, $lastMonth);
        }

        throw new \InvalidArgumentException('Invalid role specified');
    }

}

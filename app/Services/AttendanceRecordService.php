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

        $daynow = \Carbon\Carbon::now()->format('d');
        $firstDayofCurrentMonth = \Carbon\Carbon::now()->startOfMonth()->toDateTimeString();
        $firstDayofPreviousMonth = \Carbon\Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastDayofCurrentMonth = \Carbon\Carbon::now()->endOfMonth()->toDateTimeString();

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
    


}

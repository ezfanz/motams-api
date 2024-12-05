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

    public function getLateAttendanceRecords(int $userId): array
    {
        $dayNow = now()->format('d');
        $firstDayOfCurrentMonth = now()->startOfMonth()->toDateTimeString();
        $firstDayOfPreviousMonth = now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastDayOfCurrentMonth = now()->endOfMonth()->toDateTimeString();

        $startDay = $dayNow > 10 ? $firstDayOfCurrentMonth : $firstDayOfPreviousMonth;

        return $this->attendanceRecordRepository->fetchLateAttendanceRecords($userId, $startDay, $lastDayOfCurrentMonth);
    }

    public function getAbsentRecords(int $userId): array
    {
        $dayNow = Carbon::now()->format('d');
        $firstDayOfCurrentMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $firstDayOfPreviousMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastDayOfCurrentMonth = Carbon::now()->endOfMonth()->toDateTimeString();

        $startDay = $dayNow > 10 ? $firstDayOfCurrentMonth : $firstDayOfPreviousMonth;

        return $this->attendanceRecordRepository->fetchAbsentRecords($userId, $startDay, $lastDayOfCurrentMonth);
    }

    public function getEarlyLeaveRecords(int $userId): array
    {
        $dayNow = now()->format('d');
        $firstDayOfCurrentMonth = now()->startOfMonth()->toDateTimeString();
        $firstDayOfPreviousMonth = now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastDayOfCurrentMonth = now()->endOfMonth()->toDateTimeString();

        $startDay = $dayNow > 10 ? $firstDayOfCurrentMonth : $firstDayOfPreviousMonth;

        return $this->attendanceRecordRepository->fetchEarlyLeaveRecords($userId, $startDay, $lastDayOfCurrentMonth);
    }


}

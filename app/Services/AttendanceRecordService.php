<?php

namespace App\Services;

use App\Repositories\AttendanceRecordRepository;
use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

}

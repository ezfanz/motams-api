<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\AttendanceRecordService;
use App\Http\Requests\Attendance\AttendanceRecordRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\AttendanceRecordResource;


class AttendanceRecordController extends Controller
{
    protected $attendanceRecordService;

    public function __construct(AttendanceRecordService $attendanceRecordService)
    {
        $this->attendanceRecordService = $attendanceRecordService;
    }

    public function index(): JsonResponse
    {
        $records = $this->attendanceRecordService->getAllAttendanceRecords();
        return ResponseHelper::success(AttendanceRecordResource::collection($records), 'Attendance records retrieved successfully');
    }

    public function show($id): JsonResponse
    {
        try {
            $record = $this->attendanceRecordService->getAttendanceRecordById($id);
            return ResponseHelper::success(new AttendanceRecordResource($record), 'Attendance record retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error('Attendance record not found', 404);
        }
    }

    public function store(AttendanceRecordRequest $request): JsonResponse
    {
        $record = $this->attendanceRecordService->createAttendanceRecord($request->validated());
        return ResponseHelper::success($record, 'Attendance record created successfully', 201);
    }

    public function update(AttendanceRecordRequest $request, $id): JsonResponse
    {
        try {
            $record = $this->attendanceRecordService->updateAttendanceRecord($id, $request->validated());
            return ResponseHelper::success($record, 'Attendance record updated successfully');
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error('Attendance record not found for update', 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->attendanceRecordService->deleteAttendanceRecord($id);
            return ResponseHelper::success(null, 'Attendance record deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error('Attendance record not found for deletion', 404);
        }
    }

    public function listTidakHadir(): JsonResponse
    {
        $records = $this->attendanceRecordService->getRecordsByStatus(1); // Status ID for Tidak Hadir
        return ResponseHelper::success(AttendanceRecordResource::collection($records), 'Tidak Hadir records retrieved successfully');
    }

    public function listDatangLewat(): JsonResponse
    {
        $records = $this->attendanceRecordService->getRecordsByStatus(2); // Status ID for Datang Lewat
        return ResponseHelper::success(AttendanceRecordResource::collection($records), 'Datang Lewat records retrieved successfully');
    }

    public function listBalikAwal(): JsonResponse
    {
        $records = $this->attendanceRecordService->getRecordsByStatus(3); // Status ID for Balik Awal
        return ResponseHelper::success(AttendanceRecordResource::collection($records), 'Balik Awal records retrieved successfully');
    }
}

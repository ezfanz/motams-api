<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\AttendanceRecordService;
use App\Http\Requests\Attendance\AttendanceRecordRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\User;


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
        $userId = Auth::id(); // Fetch authenticated user ID
        $records = $this->attendanceRecordService->getAbsentRecords($userId);
        return ResponseHelper::success($records, 'Tidak Hadir records retrieved successfully');
    }

    public function listDatangLewat(): JsonResponse
    {
        $userId = Auth::id();
        $records = $this->attendanceRecordService->getLateAttendanceRecords($userId);
        return ResponseHelper::success($records, 'Datang Lewat records retrieved successfully');
    }

    public function listBalikAwal(): JsonResponse
    {
        $records = $this->attendanceRecordService->getRecordsByStatus(3); // Status ID for Balik Awal
        return ResponseHelper::success(AttendanceRecordResource::collection($records), 'Balik Awal records retrieved successfully');
    }

    public function getAttendanceLogs($idpeg)
    {
        // Validate $idpeg is numeric
        if (!is_numeric($idpeg)) {
            Log::error("Invalid User ID provided: {$idpeg}");
            return ResponseHelper::error('Invalid User ID. Must be a numeric value.', 400);
        }

        // Check if user exists and is not soft-deleted
        $staffId = User::whereNull('deleted_at')
            ->where('id', $idpeg)
            ->value('staff_id');

        if (!$staffId) {
            Log::warning("Staff ID not found for User ID: {$idpeg}");
            return ResponseHelper::error('Staff ID not found for the given user.', 404);
        }

        // Call service to get logs
        Log::info("Fetching attendance logs for User ID: {$idpeg}, Staff ID: {$staffId}");
        $logs = $this->attendanceRecordService->getAttendanceLogs($idpeg, $staffId);

        return ResponseHelper::success($logs, 'Attendance logs retrieved successfully');
    }


}

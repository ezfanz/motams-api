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
use App\Models\ReasonTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;


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

    public function listAttendanceRecords(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $type = $request->input('type'); // 'late', 'early', or 'absent'

        try {
            // Get attendance records based on the type
            $records = $this->attendanceRecordService->getAttendanceRecords($userId, $type);

            // Determine the success message based on the type
            $message = match ($type) {
                'late' => 'Datang Lewat records retrieved successfully',
                'back-early' => 'Balik Awal records retrieved successfully',
                'absent' => 'Tidak Hadir records retrieved successfully',
                default => throw new \InvalidArgumentException('Invalid type specified.')
            };

            return ResponseHelper::success($records, $message);
        } catch (\InvalidArgumentException $e) {
            // Handle invalid type error
            return ResponseHelper::error($e->getMessage());
        } catch (\Exception $e) {
            // Handle any other unexpected errors
            return ResponseHelper::error('Failed to retrieve attendance records.', $e->getCode());
        }
    }


    public function getAttendanceLogs($idpeg)
    {
        // Validate $idpeg is numeric
        if (!is_numeric($idpeg)) {
            Log::error("Invalid User ID provided: {$idpeg}");
            return ResponseHelper::error('Invalid User ID. Must be a numeric value.', 400);
        }

        // Check if user exists and is not soft-deleted
        $staffId = User::where('is_deleted', '!=', 1)
            ->where('id', $idpeg)
            ->value('staffid');

        if (!$staffId) {
            Log::warning("Staff ID not found for User ID: {$idpeg}");
            return ResponseHelper::error('Staff ID not found for the given user.', 404);
        }

        // Call service to get logs
        Log::info("Fetching attendance logs for User ID: {$idpeg}, Staff ID: {$staffId}");
        $logs = $this->attendanceRecordService->getAttendanceLogs($idpeg, $staffId);

        return ResponseHelper::success($logs, 'Attendance logs retrieved successfully');
    }

    /**
     * Get review counts for the authenticated user based on their role.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getReviewCounts(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $roleId = Auth::user()->roles()->first()?->id; // Assuming roles are managed via Spatie package
        
        // dd($roleId);

        if (!$roleId) {
            return ResponseHelper::error('Role not found for the authenticated user.', 400);
        }

        // try {
            $reviewCount = $this->attendanceRecordService->getReviewCount($userId, $roleId);
            return ResponseHelper::success(['review_count' => $reviewCount], 'Review count retrieved successfully');
        // } catch (\InvalidArgumentException $e) {
        //     return ResponseHelper::error($e->getMessage(), 400);
        // } catch (\Exception $e) {
        //     return ResponseHelper::error('Failed to retrieve review counts.', 500);
        // }
    }


     /**
     * Get approval counts for the authenticated user based on their role.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getApprovalCounts(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $roleId = Auth::user()->roles()->first()?->id; // Assuming roles are managed via Spatie package
        
        // dd($roleId);

        if (!$roleId) {
            return ResponseHelper::error('Role not found for the authenticated user.', 400);
        }

        // try {
            $approvalCount = $this->attendanceRecordService->getApprovalCount($userId, $roleId);
            return ResponseHelper::success(['approval_count' => $approvalCount], 'Approval count retrieved successfully');
        // } catch (\InvalidArgumentException $e) {
        //     return ResponseHelper::error($e->getMessage(), 400);
        // } catch (\Exception $e) {
        //     return ResponseHelper::error('Failed to retrieve review counts.', 500);
        // }
    }



}

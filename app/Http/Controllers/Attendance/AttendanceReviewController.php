<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceReviewIndexRequest;
use App\Http\Requests\Attendance\BatchReviewRequest;
use App\Http\Requests\Attendance\AttendanceStatusSummaryRequest;
use App\Http\Requests\Attendance\ProcessAttendanceReviewRequest;
use App\Http\Requests\Attendance\BatchProcessAttendanceReviewRequest;
use App\Services\AttendanceReviewService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceReviewController extends Controller
{
    protected $service;

    public function __construct(AttendanceReviewService $service)
    {
        $this->service = $service;
    }

    public function index(AttendanceReviewIndexRequest $request): JsonResponse
    {
        $filters = $request->only(['status', 'month', 'year']);
        $filters['user_id'] = Auth::id();
        $filters['role_id'] = Auth::user()->role_id;

        $attendanceRecords = $this->service->getAttendanceRecordsForReview($filters);

        return response()->json([
            'status' => 'success',
            'message' => 'Pending Attendance review records retrieved successfully.',
            'data' => $attendanceRecords,
        ]);
    }

    public function batchUpdate(BatchReviewRequest $request): JsonResponse
    {
        $this->service->batchUpdateReviewStatus($request->validated());
        return ResponseHelper::success(null, 'Attendance records updated successfully');
    }

    public function getMonthlyStatusSummary(AttendanceStatusSummaryRequest $request): JsonResponse
    {
        $summary = $this->service->getMonthlyStatusSummary($request->validated()['month'], $request->validated()['year']);
        return ResponseHelper::success($summary, 'Attendance status summary retrieved successfully');
    }

   /**
     * API to process attendance review.
     */
    public function processReview(ProcessAttendanceReviewRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id(); // Get logged-in user ID

        $result = $this->service->processReview($validated, $userId);

        if ($result['status']) {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $result['message']], 400);
    }

    /**
     * API to fetch review details by ID.
     */
    public function getReviewDetails(int $id)
    {
        $details = $this->service->getReviewDetails($id);

        if (!$details) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $details], 200);
    }

    public function batchProcess(BatchProcessAttendanceReviewRequest $request)
    {
        $validated = $request->validated(); // Validate incoming data
        $userId = Auth::id(); // Get authenticated user ID
        $result = $this->service->processBatchReview($validated, $userId);

        if ($result['status']) {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $result['message']], 400);
    }

}

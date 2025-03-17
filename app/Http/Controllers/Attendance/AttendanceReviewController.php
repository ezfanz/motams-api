<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceReviewIndexRequest;
use App\Http\Requests\Attendance\BatchReviewRequest;
use App\Http\Requests\Attendance\AttendanceStatusSummaryRequest;
use App\Http\Requests\Attendance\ProcessAttendanceReviewRequest;
use App\Http\Requests\Attendance\BatchProcessAttendanceReviewRequest;
use App\Services\AttendanceReviewService;
use App\Models\Status;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
    
        // Convert status string to integer using Status model
        if (!empty($filters['status'])) {
            $filters['status'] = Status::where('diskripsi', $filters['status'])->value('id');
        }
    
        $attendanceRecords = $this->service->getAttendanceRecordsForReview($filters);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Pending Attendance review records retrieved successfully.',
            'data' => $attendanceRecords,
        ]);
    }

    public function batchUpdate(BatchReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = Auth::id(); // Get logged-in user ID
    
        $result = $this->service->batchUpdateReviewStatus($validated, $userId);
    
        if ($result['status']) {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }
    
        return response()->json(['status' => 'error', 'message' => $result['message']], 400);
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

   

    public function individualReview(Request $request)
    {
        
        $userId = Auth::id();
        $roleId = Auth::user()->role_id;

        // Get today's date
        $dayNow = Carbon::now()->format('d');
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        $previousMonth = Carbon::now()->subMonth()->format('m');

        // Apply the same review count logic to determine the date range
        if ($dayNow > 10) {
            $filters = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'month' => $currentMonth,
                'year' => $currentYear,
            ];
        } else {
            $filters = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'month' => [$previousMonth, $currentMonth], // Fetch for both months
                'year' => $currentYear,
            ];
        }

        // Fetch attendance records for the user
        $attendanceRecords = $this->service->getAttendanceRecordsForReview($filters);

        return response()->json([
            'status' => 'success',
            'message' => 'Auto-fetched attendance review records retrieved successfully.',
            'data' => $attendanceRecords,
        ]);
    }


}

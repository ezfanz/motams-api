<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Attendance\BatchVerificationRequest;
use App\Http\Requests\Attendance\BatchApproveRequest;
use App\Services\VerificationService;
use App\Http\Requests\Attendance\BatchReviewRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;


class VerificationController extends Controller
{
    protected $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    // Retrieve all attendance records for verification
    public function index(Request $request)
    {
        $records = $this->verificationService->getRecordsForVerification($request->query());
        return ResponseHelper::success($records, 'Verification records retrieved successfully');
    }

    // Get the list of status options for verification
    public function getStatusOptions()
    {
        $options = $this->verificationService->getStatusOptions();
        return ResponseHelper::success($options, 'Verification status options retrieved successfully');
    }

    // Submit batch verification
    public function batchUpdate(BatchVerificationRequest $request)
    {
        $this->verificationService->batchUpdateVerificationStatus($request->validated());
        return ResponseHelper::success(null, 'Records updated successfully');
    }

    // Get monthly summary of verifications by status
    public function getMonthlySummary(Request $request)
    {
        $summary = $this->verificationService->getMonthlyStatusSummary($request->query('month'), $request->query('year'));
        return ResponseHelper::success($summary, 'Monthly verification summary retrieved successfully');
    }

    /**
     * Batch approval process for attendance verification.
     */
    public function batchApprove(BatchApproveRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id(); // Authenticated user ID
        $response = $this->verificationService->processBatchApproval($validated, $userId);

        if ($response['status']) {
            return response()->json(['status' => 'success', 'message' => $response['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $response['message']], 400);
    }

        /**
     * Batch review process for attendance.
     */
    public function batchReview(BatchReviewRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id(); // Authenticated user ID
        $response = $this->verificationService->processBatchReview($validated, $userId);

        if ($response['status']) {
            return response()->json(['status' => 'success', 'message' => $response['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $response['message']], 400);
    }

}

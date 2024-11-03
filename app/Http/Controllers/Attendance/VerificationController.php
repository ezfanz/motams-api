<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Attendance\BatchVerificationRequest;
use App\Services\VerificationService;
use App\Helpers\ResponseHelper;


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
}

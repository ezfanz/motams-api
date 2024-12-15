<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OfficeLeaveApprovalService;

use App\Http\Requests\Leave\FilterLeaveApprovalRequest;
use App\Http\Requests\Leave\BatchApprovalRequest;
use App\Http\Requests\Leave\MonthlyStatusSummaryRequest;
use App\Http\Requests\Leave\OfficeLeaveApprovalRequest;

use Illuminate\Support\Facades\Auth;



class OfficeLeaveApprovalController extends Controller
{
    protected $service;

    public function __construct(OfficeLeaveApprovalService $service)
    {
        $this->service = $service;
    }

    public function index(FilterLeaveApprovalRequest $request)
    {
        $leaveRequests = $this->service->getPendingApprovals($request->validated());
        return response()->json(['status' => 'success', 'data' => $leaveRequests]);
    }

    public function batchUpdate(BatchApprovalRequest $request)
    {
        $this->service->batchUpdateApprovalStatus($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Leave requests updated successfully']);
    }

    public function getMonthlySummary(MonthlyStatusSummaryRequest $request)
    {
        $summary = $this->service->getMonthlyApprovalSummary($request->input('start_date'), $request->input('end_date'));
        return response()->json(['status' => 'success', 'data' => $summary]);
    }

     /**
     * Approve or Reject Office Leave Requests.
     */
    public function approve(OfficeLeaveApprovalRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id();

        $result = $this->service->approveLeaveRequest($validated, $userId);

        if ($result['status']) {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $result['message']], 400);
    }
}

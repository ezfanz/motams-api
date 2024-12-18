<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OfficeLeaveApprovalService;

use App\Http\Requests\Leave\FilterLeaveApprovalRequest;
use App\Http\Requests\Leave\BatchApprovalRequest;
use App\Http\Requests\Leave\MonthlyStatusSummaryRequest;
use App\Http\Requests\Leave\OfficeLeaveApprovalRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Leave\GetSupervisedApprovalStatusRequest;



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

     /**
     * Batch update leave approvals.
     *
     * @param BatchApprovalRequest $request
     * @return JsonResponse
     */

    public function batchUpdate(BatchApprovalRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $result = $this->service->batchUpdateApprovalStatus($userId, $request->validated());

        if ($result['status'] === 'success') {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $result['message']], 400);
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

    public function getSupervisedApprovalStatus(GetSupervisedApprovalStatusRequest $request): JsonResponse
    {
        $userId = Auth::id();

        // Fetch filters from request
        $filters = [
            'pegawai_id' => $request->input('pegawai_id'),
            'month_start' => $request->input('month_start'),
            'month_end' => $request->input('month_end'),
        ];

        // Fetch approval statuses
        $statuses = $this->service->fetchSupervisedApprovalStatuses($filters, $userId);

        return response()->json([
            'status' => 'success',
            'data' => $statuses,
        ]);
    }

}

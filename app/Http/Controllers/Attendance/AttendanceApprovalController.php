<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceApprovalService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Attendance\BatchProcessAttendanceApprovalRequest;

class AttendanceApprovalController extends Controller
{
    protected $service;

    public function __construct(AttendanceApprovalService $service)
    {
        $this->service = $service;
    }

    public function getApprovalList(Request $request)
    {
        $userId = Auth::id(); // Authenticated user ID
        $roleId = Auth::user()->role_id; // User role ID
        $monthSearch = $request->query('month'); // Optional month
        $yearSearch = $request->query('year');   // Optional year

        // Call the service to fetch the approval list
        $approvalList = $this->service->getApprovalList($userId, $roleId, $monthSearch, $yearSearch);

        return ResponseHelper::success($approvalList, 'Pending Approval list retrieved successfully');
    }

    public function batchProcess(BatchProcessAttendanceApprovalRequest $request)
    {
        $validated = $request->validated(); // Validate incoming data
        $userId = Auth::id(); // Get authenticated user ID
        $result = $this->service->processBatchApprove($validated, $userId);

        if ($result['status']) {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $result['message']], 400);
    }

}

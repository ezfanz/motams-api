<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceApprovalService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;

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
        $monthSearch = $request->query('month', now()->month); // Default to current month
        $yearSearch = $request->query('year', now()->year); // Default to current year

        // Call the service to fetch the approval list
        $approvalList = $this->service->getApprovalList($userId, $roleId, $monthSearch, $yearSearch);

        return ResponseHelper::success($approvalList, 'Pending Approval list retrieved successfully');
    }

}

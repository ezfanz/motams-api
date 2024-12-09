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
        $roleId = Auth::user()->roles()->first()?->id; // User role ID
        $dayNow = now()->format('d');
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonthNoOverflow()->format('Y-m');

        // Call the service to fetch the approval list
        $approvals = $this->service->fetchApprovalList($userId, $roleId, $dayNow, $currentMonth, $lastMonth);

        return ResponseHelper::success($approvals, 'Approval list retrieved successfully');
    }
}

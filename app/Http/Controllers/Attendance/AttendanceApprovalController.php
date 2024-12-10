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
        $monthSearch = $request->query('month', now()->format('Y-m'));

        // Call the service to fetch the approval list
        $approvalList = $this->service->getApprovalList($userId, $roleId, $monthSearch);

        return ResponseHelper::success($approvalList, 'Approval list retrieved successfully');
    }

}

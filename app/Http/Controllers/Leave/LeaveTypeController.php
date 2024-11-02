<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Services\LeaveTypeService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

class LeaveTypeController extends Controller
{
    protected $service;

    public function __construct(LeaveTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the leave types.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $leaveTypes = $this->service->getAllLeaveTypes();
        return ResponseHelper::success($leaveTypes, 'Leave types retrieved successfully');
    }
}

<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\GetLeaveStatusRequest;
use App\Services\OfficeLeaveStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OfficeLeaveStatusController extends Controller
{
    protected $officeLeaveStatusService;

    public function __construct(OfficeLeaveStatusService $officeLeaveStatusService)
    {
        $this->officeLeaveStatusService = $officeLeaveStatusService;
    }

   /**
     * Fetch the status of leave requests.
     *
     * @param GetLeaveStatusRequest $request
     * @return JsonResponse
     */
    public function getLeaveStatus(GetLeaveStatusRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $filters = $request->validated();

        $leaveStatuses = $this->officeLeaveStatusService->fetchLeaveStatuses($filters, $userId);

        return response()->json([
            'status' => 'success',
            'data' => $leaveStatuses,
        ]);
    }
}

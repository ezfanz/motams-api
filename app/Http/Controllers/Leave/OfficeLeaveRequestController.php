<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Services\OfficeLeaveRequestService;
use App\Http\Requests\Leave\OfficeLeaveRequestRequest;
use App\Http\Requests\Leave\OfficeLeaveRequestFormRequest;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OfficeLeaveRequestController extends Controller
{
    protected $service;

    public function __construct(OfficeLeaveRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the leave requests.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $leaveRequests = $this->service->getAllLeaveRequests();
        return ResponseHelper::success($leaveRequests, 'Leave requests retrieved successfully');
    }

      /**
     * Store a new office leave request.
     *
     * @param OfficeLeaveRequestFormRequest $request
     * @return JsonResponse
     */
    public function store(OfficeLeaveRequestFormRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $data = $request->validated();

        $result = $this->service->createLeaveRequest($userId, $data);

        if ($result['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'message' => $result['message']
            ], 201);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message']
        ], 400);
    }

    /**
     * Display the specified leave request.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $leaveRequest = $this->service->getLeaveRequestById($id);
        if ($leaveRequest) {
            return ResponseHelper::success($leaveRequest, 'Leave request retrieved successfully');
        }
        return ResponseHelper::error('Leave request not found', 404);
    }

    /**
     * Update the specified leave request.
     *
     * @param OfficeLeaveRequestRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(OfficeLeaveRequestRequest $request, int $id): JsonResponse
    {
        $leaveRequest = $this->service->updateLeaveRequest($id, $request->validated());
        if ($leaveRequest) {
            return ResponseHelper::success($leaveRequest, 'Leave request updated successfully');
        }
        return ResponseHelper::error('Leave request not found', 404);
    }

    /**
     * Remove the specified leave request.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if ($this->service->deleteLeaveRequest($id)) {
            return ResponseHelper::success(null, 'Leave request deleted successfully');
        }
        return ResponseHelper::error('Leave request not found', 404);
    }

      /**
     * Display a listing of the leave requests for a specific month and year.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByMonth(Request $request): JsonResponse
    {
        // Use simple query validation for month and year parameters
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:' . now()->year,
        ]);

        $month = $validated['month'];
        $year = $validated['year'];

        $leaveRequests = $this->service->getLeaveRequestsByMonth($month, $year);

        return ResponseHelper::success($leaveRequests, 'Leave requests retrieved successfully for the specified month');
    }

    public function countApproval(Request $request): JsonResponse
    {
        // Get the current user ID
        $userId = Auth::user()->id;

        // If there's a passed parameter for user ID (from the box), use it
        if ($request->has('idpeg') && is_numeric($request->input('idpeg'))) {
            $userId = $request->input('idpeg');
        }

        // Call the service to count approvals
        $approvalCount = $this->service->countApprovalsForUser($userId);

        // Return the count as a response
        return ResponseHelper::success(['count' => $approvalCount], 'Approval count retrieved successfully');
    }
}

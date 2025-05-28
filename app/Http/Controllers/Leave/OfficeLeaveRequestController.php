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
        return ResponseHelper::success($leaveRequests, 'Senarai permohonan keluar pejabat berjaya dipaparkan');
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
            return ResponseHelper::success($leaveRequest, 'Maklumat permohonan keluar pejabat berjaya dipaparkan');
        }
        return ResponseHelper::error('Permohonan keluar pejabat tidak dijumpai', 404);
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
            return ResponseHelper::success($leaveRequest, 'Permohonan keluar pejabat berjaya dikemaskini');
        }
        return ResponseHelper::error('Permohonan keluar pejabat tidak dijumpai', 404);
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
            return ResponseHelper::success(null, 'Permohonan keluar pejabat berjaya dipadam');
        }
        return ResponseHelper::error('Permohonan keluar pejabat tidak dijumpai', 404);
    }

      /**
     * Display a listing of the leave requests for a specific month and year.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByMonth(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:' . now()->year,
        ]);
    
        $month = $validated['month'];
        $year = $validated['year'];
    
        $leaveRequests = $this->service->getLeaveRequestsByMonth($month, $year);
    
        return ResponseHelper::success($leaveRequests, 'Senarai permohonan untuk bulan yang dipilih berjaya dipaparkan');
    }

    public function countApproval(Request $request): JsonResponse
    {
        // Get the current user
        $user = Auth::user();
        $userId = $user->id;
        $roleId = $user->role_id;
    
        // If there's a passed parameter for user ID (from the box), use it
        if ($request->has('idpeg') && is_numeric($request->input('idpeg'))) {
            $userId = $request->input('idpeg');
        }
    
        // Call the service to count approvals based on role
        $approvalCount = $this->service->countApprovalsForUser($userId, $roleId);
    
        // Return the count as a response
        return ResponseHelper::success(['count' => $approvalCount], 'Jumlah permohonan keluar pejabat yang menunggu kelulusan berjaya dipaparkan');
    }

    public function getAvailableApprovers(int $userId): JsonResponse
    {
        $approvers = $this->service->getAvailableApprovers($userId);

        return ResponseHelper::success($approvers, 'Senarai pelulus berjaya dipaparkan');
    }

    
}

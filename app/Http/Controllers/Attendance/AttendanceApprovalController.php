<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceApprovalService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Attendance\BatchProcessAttendanceApprovalRequest;
use Carbon\Carbon;

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

    public function individualReview(Request $request)
    {
        
        $userId = Auth::id();
        $roleId = Auth::user()->role_id;

        // Get today's date
        $dayNow = Carbon::now()->format('d');
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        $previousMonth = Carbon::now()->subMonth()->format('m');

        // Apply the same review count logic to determine the date range
        if ($dayNow > 10) {
            $filters = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'month' => $currentMonth,
                'year' => $currentYear,
            ];
        } else {
            $filters = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'month' => [$previousMonth, $currentMonth], // Fetch for both months
                'year' => $currentYear,
            ];
        }

        // Fetch attendance records for the user
        $attendanceRecords = $this->service->getAttendanceRecordsForApproval($filters);

        return response()->json([
            'status' => 'success',
            'message' => 'Auto-fetched attendance approval records retrieved successfully.',
            'data' => $attendanceRecords,
        ]);
    }

}

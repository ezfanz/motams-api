<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceConfirmationService;
use App\Http\Requests\Attendance\ProcessAttendanceConfirmationRequest;

class AttendanceConfirmationController extends Controller
{
    protected $service;

    public function __construct(AttendanceConfirmationService $service)
    {
        $this->service = $service;
    }

    /**
     * Fetch confirmation details for a given transaction ID.
     */
    public function getConfirmationDetails(int $id)
    {
        $details = $this->service->getConfirmationDetails($id);

        if (!$details) {
            return response()->json(['status' => 'error', 'message' => 'Transaction not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $details], 200);
    }

    /**
     * Process confirmation for a transaction.
     */
    public function processConfirmation(ProcessAttendanceConfirmationRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id();

        $result = $this->service->processConfirmation($validated, $userId);

        if ($result['status']) {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }

        return response()->json(['status' => 'error', 'message' => $result['message']], 400);
    }
}

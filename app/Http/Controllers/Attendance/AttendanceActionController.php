<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\EarlyDepartureRequest;
use App\Http\Requests\Attendance\LateArrivalRequest;
use App\Services\AttendanceActionService;
use App\Http\Requests\Attendance\AbsentRequest;

class AttendanceActionController extends Controller
{
    private $attendanceActionService;

    public function __construct(AttendanceActionService $attendanceActionService)
    {
        $this->attendanceActionService = $attendanceActionService;
    }

    public function handleEarlyDeparture(EarlyDepartureRequest $request)
    {
        $response = $this->attendanceActionService->handleEarlyDeparture($request->validated());
        return response()->json($response['data'], $response['status']);
    }

    public function handleLateArrival(LateArrivalRequest $request)
    {
        $response = $this->attendanceActionService->handleLateArrival($request->validated());
        return response()->json($response['data'], $response['status']);
    }

    public function handleAbsent(AbsentRequest $request)
    {
        $response = $this->attendanceActionService->handleAbsent($request->validated());
        return response()->json($response['data'], $response['status']);
    }
}

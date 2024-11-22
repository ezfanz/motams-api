<?php

namespace App\Http\Controllers\Attendance;

use Illuminate\Http\Request;
use App\Services\AttendanceLogService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class AttendanceLogController extends Controller
{
    protected $service;

    public function __construct(AttendanceLogService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $userId = Auth::id();
        $logs = $this->service->getAllLogsForUser($userId);

        return response()->json(['status' => 'success', 'data' => $logs]);
    }

    public function show($date)
    {
        $userId = Auth::id();
        $log = $this->service->getAttendanceLog($userId, $date);

        if (!$log) {
            return response()->json(['status' => 'error', 'message' => 'Attendance log not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $log]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i:s',
            'time_out' => 'nullable|date_format:H:i:s|after:time_in',
        ]);

        $userId = Auth::id();
        $log = $this->service->createOrUpdateAttendanceLog($userId, $data);

        return response()->json(['status' => 'success', 'data' => $log]);
    }
}

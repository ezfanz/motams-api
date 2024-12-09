<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\AttendanceStatusService;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

class AttendanceStatusController extends Controller
{
    protected $attendanceStatusService;

    public function __construct(AttendanceStatusService $attendanceStatusService)
    {
        $this->attendanceStatusService = $attendanceStatusService;
    }

    public function getStatusList(Request $request)
    {
        try {
            // Fetch data from the service
            $month = $request->query('month', now()->format('Y-m'));
            $data = $this->attendanceStatusService->fetchStatusList($month);

            return ResponseHelper::success($data, 'Status list retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}

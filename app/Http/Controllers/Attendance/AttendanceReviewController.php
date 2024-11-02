<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceReviewIndexRequest;
use App\Http\Requests\Attendance\BatchReviewRequest;
use App\Http\Requests\Attendance\AttendanceStatusSummaryRequest;
use App\Services\AttendanceReviewService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

class AttendanceReviewController extends Controller
{
    protected $service;

    public function __construct(AttendanceReviewService $service)
    {
        $this->service = $service;
    }

    public function index(AttendanceReviewIndexRequest $request): JsonResponse
    {
        $records = $this->service->getAttendanceRecordsForReview($request->validated());
        return ResponseHelper::success($records, 'Attendance records retrieved successfully');
    }

    public function batchUpdate(BatchReviewRequest $request): JsonResponse
    {
        $this->service->batchUpdateReviewStatus($request->validated());
        return ResponseHelper::success(null, 'Attendance records updated successfully');
    }

    public function getMonthlyStatusSummary(AttendanceStatusSummaryRequest $request): JsonResponse
    {
        $summary = $this->service->getMonthlyStatusSummary($request->validated()['month'], $request->validated()['year']);
        return ResponseHelper::success($summary, 'Attendance status summary retrieved successfully');
    }
}

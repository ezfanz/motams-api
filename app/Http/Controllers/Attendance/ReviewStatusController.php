<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\ReviewStatusService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

class ReviewStatusController extends Controller
{
    protected $reviewStatusService;

    public function __construct(ReviewStatusService $reviewStatusService)
    {
        $this->reviewStatusService = $reviewStatusService;
    }

    /**
     * Display a list of review status options.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $statuses = $this->reviewStatusService->getAllReviewStatuses();
        return ResponseHelper::success($statuses, 'Review statuses retrieved successfully');
    }
}

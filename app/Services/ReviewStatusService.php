<?php

namespace App\Services;

use App\Repositories\ReviewStatusRepository;

class ReviewStatusService
{
    protected $reviewStatusRepository;

    public function __construct(ReviewStatusRepository $reviewStatusRepository)
    {
        $this->reviewStatusRepository = $reviewStatusRepository;
    }

    /**
     * Get all review statuses.
     *
     * @return array
     */
    public function getAllReviewStatuses()
    {
        return $this->reviewStatusRepository->getAll();
    }
}

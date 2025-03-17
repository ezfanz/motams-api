<?php

namespace App\Services;

use App\Repositories\StatusRepository;

class StatusService
{
    protected $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    /**
     * Get all statuses.
     */
    public function getAllStatuses()
    {
        return $this->statusRepository->getAllStatuses();
    }
}

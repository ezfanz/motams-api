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


     /**
     * Get statuses based on user role.
     */
    public function getStatusesByRole(?int $roleId)
    {
        return $this->statusRepository->getStatusesByRole($roleId);
    }

     /**
     * Get Semakan (Review) statuses.
     */
    public function getSemakanStatuses()
    {
        return $this->statusRepository->getSemakanStatuses();
    }

    /**
     * Get Pengesahan (Approval) statuses.
     */
    public function getPengesahanStatuses()
    {
        return $this->statusRepository->getPengesahanStatuses();
    }
}

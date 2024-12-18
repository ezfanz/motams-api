<?php

namespace App\Services;

use App\Repositories\OfficeLeaveStatusRepository;

class OfficeLeaveStatusService
{
    protected $repository;

    public function __construct(OfficeLeaveStatusRepository $repository)
    {
        $this->repository = $repository;
    }

      /**
     * Fetch leave statuses with filters.
     *
     * @param array $filters
     * @param int $userId
     * @return array
     */
    public function fetchLeaveStatuses(array $filters, int $userId): array
    {
        return $this->repository->getLeaveStatuses($filters, $userId);
    }

}

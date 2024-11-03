<?php

namespace App\Services;

use App\Repositories\OfficeLeaveApprovalRepository;

class OfficeLeaveApprovalService
{
    protected $repository;

    public function __construct(OfficeLeaveApprovalRepository $repository,)
    {
        $this->repository = $repository;
    }

    public function getPendingApprovals(array $filters)
    {
        return $this->repository->filterPendingApprovals($filters);
    }

    public function getStatusOptions()
    {
        return $this->repository->getAllStatuses();
    }

    public function batchUpdateApprovalStatus(array $data)
    {
        return $this->repository->updateApprovalStatusForRequests($data['request_ids'], $data['approval_status_id'], $data['approval_notes']);
    }

    public function getMonthlyApprovalSummary($startDate, $endDate)
    {
        return $this->repository->getSummaryByDateRange($startDate, $endDate);
    }
}

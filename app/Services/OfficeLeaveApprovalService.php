<?php

namespace App\Services;

use App\Repositories\OfficeLeaveApprovalRepository;
use Carbon\Carbon;

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

     /**
     * Approve or Reject a Single Leave Request.
     */
    public function approveLeaveRequest(array $data, int $userId): array
    {
        $leaveId = $data['leave_id'];
        $status = $data['status'];
        $notes = $data['approval_notes'];
        $timestamp = Carbon::now();

        // Validate approver permissions
        $userRole = $this->repository->getUserRole($userId);
        if (!in_array($userRole, [3, 6, 7, 9, 10, 12, 13, 16, 17])) {
            return ['status' => false, 'message' => 'Unauthorized role for approval'];
        }

        // Update Leave Request
        $updated = $this->repository->updateLeaveRequest($leaveId, $userId, $status, $notes, $timestamp);

        if ($updated) {
            $message = $status == 16 ? 'Leave approved successfully.' : 'Leave rejected successfully.';
            return ['status' => true, 'message' => $message];
        }

        return ['status' => false, 'message' => 'Failed to process leave approval.'];
    }
}

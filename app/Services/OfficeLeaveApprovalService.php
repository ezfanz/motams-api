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

/**
     * Batch update approval statuses for office leave requests.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function batchUpdateApprovalStatus(int $userId, array $data): array
    {
        $approvalStatus = $data['statusalasan'];
        $approvalNotes = $data['catatanpengesah'] ?? null;
        $requestIds = $data['leave_id'];

        $result = $this->repository->updateApprovalStatusForRequests($userId, $requestIds, $approvalStatus, $approvalNotes);

        if ($result) {
            return [
                'status' => 'success',
                'message' => 'Leave requests updated successfully.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to update leave requests.',
        ];
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


    public function fetchSupervisedApprovalStatuses(array $filters, int $userId): array
    {
        // Get department ID based on user ID
        $departmentId = $this->repository->getDepartmentByUserId($userId);

        return $this->repository->getSupervisedApprovalStatuses($filters, $departmentId);
    }
}

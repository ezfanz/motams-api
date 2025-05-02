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

    public function getPendingApprovals(int $userId, int $roleId, array $filters)
    {
        return $this->repository->filterPendingApprovals($userId, $roleId, $filters);
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
        $leaveIds = $data['leave_id'];

        // Call the repository method to update approval statuses
        $result = $this->repository->updateApprovalStatusForRequests($userId, $leaveIds, $approvalStatus, $approvalNotes);

        if ($result) {
            $message = $approvalStatus == 16
                ? 'Proses kemaskini rekod berjaya.'
                : 'Proses kemaskini rekod berjaya.';

            return [
                'status' => 'success',
                'message' => $message,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Gagal mengemas kini permintaan cuti.',
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
        $status = $data['statusalasan'];
        $notes = $data['catatanpengesah'];
        $timestamp = Carbon::now();

        // Validate approver permissions
        $userRole = $this->repository->getUserRole($userId);

        if (!in_array($userRole, [3, 6, 7, 9, 10, 12, 13, 16, 17])) {
            return ['status' => false, 'message' => 'Peranan yang tidak dibenarkan untuk kelulusan'];
        }

        // Update Leave Request
        $updated = $this->repository->updateLeaveRequest($leaveId, $userId, $status, $notes, $timestamp);

        if ($updated) {
            $message = $status == 16 ? 'Proses kemaskini rekod berjaya.' : 'Proses kemaskini rekod berjaya';
            return ['status' => true, 'message' => $message];
        }

        return ['status' => false, 'message' => 'Gagal memproses kelulusan cuti.'];
    }


    public function fetchSupervisedApprovalStatuses(array $filters, int $userId): array
    {
        // Get department ID based on user ID
        $departmentId = $this->repository->getDepartmentByUserId($userId);

        return $this->repository->getSupervisedApprovalStatuses($filters, $departmentId);
    }
}

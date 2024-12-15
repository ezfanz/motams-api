<?php

namespace App\Repositories;

use App\Models\OfficeLeaveRequest;
use App\Models\ReviewStatus;
use App\Models\User;

class OfficeLeaveApprovalRepository
{
    public function filterPendingApprovals(array $filters)
    {
        $query = OfficeLeaveRequest::with(['creator', 'leaveType', 'approvalStatus'])
            ->where('approval_status_id', null);

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['employee_name'])) {
            $query->whereHas('creator', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['employee_name'] . '%');
            });
        }

        return $query->paginate(10);
    }


    public function updateApprovalStatusForRequests(array $requestIds, $statusId, $notes = null)
    {
        return OfficeLeaveRequest::whereIn('id', $requestIds)
            ->update(['approval_status_id' => $statusId, 'approval_notes' => $notes]);
    }

    public function getAllStatuses()
    {
        return ReviewStatus::all();
    }

    public function getSummaryByDateRange($startDate, $endDate)
    {
        return OfficeLeaveRequest::with('approvalStatus', 'creator')
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('approval_status_id')
            ->map(function ($requests, $statusId) {
                return [
                    'status' => $requests->first()->approvalStatus->status ?? 'No Status',
                    'total' => $requests->count(),
                    'requests' => $requests->map(function ($request) {
                        return [
                            'employee_name' => $request->creator->name,
                            'date' => $request->date,
                            'start_time' => $request->start_time,
                            'end_time' => $request->end_time,
                            'reason' => $request->reason,
                            'status' => $request->approvalStatus->status ?? 'No Status',
                        ];
                    })
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get the role of a user by ID.
     */
    public function getUserRole(int $userId): ?int
    {
        return User::where('id', $userId)->value('role_id');
    }

    /**
     * Update a Single Office Leave Request.
     */
    public function updateLeaveRequest(int $leaveId, int $approverId, int $status, string $notes, $timestamp): bool
    {
        return OfficeLeaveRequest::where('id', $leaveId)
            ->update([
                'pelulus_id' => $approverId,
                'status_pelulus' => $status,
                'catatan_pelulus' => $notes,
                'tkh_kelulusan' => $timestamp,
                'status' => $status,
                'pengguna' => $approverId,
            ]);
    }
}

<?php

namespace App\Repositories;

use App\Models\OfficeLeaveRequest;
use App\Models\ReviewStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


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


 /**
     * Update approval statuses for leave requests in batch.
     *
     * @param int $userId
     * @param array $requestIds
     * @param int $approvalStatus
     * @param string|null $approvalNotes
     * @return bool
     */
    public function updateApprovalStatusForRequests(int $userId, array $requestIds, int $approvalStatus, ?string $approvalNotes): bool
    {
        $updateData = [
            'approval_status_id' => $approvalStatus,
            'approval_notes' => $approvalNotes,
            'approval_date' => Carbon::now(),
            'approved_by' => $userId,
            'status' => $approvalStatus,
        ];

        return OfficeLeaveRequest::whereIn('id', $requestIds)
            ->update($updateData);
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


    /**
     * Get the department ID for a given user.
     *
     * @param int $userId
     * @return int|null
     */
    public function getDepartmentByUserId(int $userId): ?String
    {
        return DB::table('users')
        ->where('id', $userId)
            ->whereNull('deleted_at') // Ensure soft delete handling
            ->value('department');
    }


    public function getSupervisedApprovalStatuses(array $filters, String $departmentId): array
    {
        $query = DB::table('office_leave_requests as olr')
        ->leftJoin('users as u', 'olr.created_by', '=', 'u.id')
        ->leftJoin('leave_types as lt', 'olr.leave_type_id', '=', 'lt.id')
        // ->leftJoin('department as d', 'u.department_id', '=', 'd.id')
        ->leftJoin('statuses as s', 'olr.status', '=', 's.id')
        ->select(
            'olr.id',
            'u.name as nama_pegawai',
            'u.position as jawatan',
            // 'd.diskripsi as deptname',
            'lt.name as jenis_leave',
            DB::raw("DATE_FORMAT(olr.date, '%d/%m/%Y') as tarikh"),
            'olr.day as hari',
            'olr.start_time',
            'olr.end_time',
            DB::raw("CONCAT(FLOOR(olr.total_hours), ' Jam ', ROUND((MOD(olr.total_hours, 1) * 60)), ' Minit') AS total_hours_minutes"),
            'olr.reason',
            's.description as disk_status',
            DB::raw("DATE_FORMAT(olr.approval_date, '%d/%m/%Y %h:%i:%s %p') as tarikh_kelulusan")
        )
            ->where('u.department', $departmentId)
            ->whereNull('olr.deleted_at');

        // Apply filters
        if (!empty($filters['pegawai_id'])) {
            $query->where('olr.created_by', $filters['pegawai_id']);
        }

        if (!empty($filters['month_start']) && !empty($filters['month_end'])) {
            $query->whereBetween('olr.date', [
                Carbon::parse("{$filters['month_start']}-01"),
                Carbon::parse("{$filters['month_end']}-01")->endOfMonth(),
            ]);
        }

        return $query->orderBy('olr.date', 'desc')->get()->toArray();
    }
}

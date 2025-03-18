<?php

namespace App\Repositories;

use App\Models\OfficeLeaveRequest;
use App\Models\ReviewStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Status;

class OfficeLeaveApprovalRepository
{
    public function filterPendingApprovals(int $userId, int $roleId, array $filters)
{
    $query = OfficeLeaveRequest::select(
        'office_leave_requests.id AS leave_id',
        'users.fullname',
        'users.jawatan',
        'office_leave_requests.leave_type_id',
        'leave_types.diskripsi as jenis_leave',
        'office_leave_requests.status', // Ensure status is selected
        DB::raw("DATE_FORMAT(office_leave_requests.date_mula, '%d/%m/%Y') AS date_mula"),
        DB::raw("DATE_FORMAT(office_leave_requests.date_tamat, '%d/%m/%Y') AS date_tamat"),
        'office_leave_requests.day_timeoff',
        'office_leave_requests.start_time',
        'office_leave_requests.end_time',
        'office_leave_requests.totalday',
        'office_leave_requests.totalhours',
        'office_leave_requests.reason',
        DB::raw("
            CONCAT(
                FLOOR(office_leave_requests.totalhours), ' Jam ', 
                ROUND((MOD(office_leave_requests.totalhours, 1) * 60)), ' Minit'
            ) AS total_hours_minutes
        "),
        DB::raw("DATE_FORMAT(office_leave_requests.tkh_mohon, '%d/%m/%Y %h:%i:%s %p') AS tarikh_mohon")
    )
    ->leftJoin('leave_types', 'office_leave_requests.leave_type_id', '=', 'leave_types.id')
    ->leftJoin('users', 'office_leave_requests.idpeg', '=', 'users.id')
    ->where('office_leave_requests.status', Status::BARU); // Ensure filtering by status 15

    if ($roleId != 3) {
        // For non-admin roles, filter by approver ID
        $query->where('office_leave_requests.pelulus_id', $userId);
    }

    // Apply filters
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $query->whereBetween('office_leave_requests.date_mula', [$filters['start_date'], $filters['end_date']]);
    }

    if (!empty($filters['employee_name'])) {
        $query->whereHas('creator', function ($q) use ($filters) {
            $q->where('fullname', 'like', '%' . $filters['employee_name'] . '%');
        });
    }

    return $query->get()->map(function ($leaveRequest) {
        // Convert status ID to meaningful text using the Status model method
        $statusText = Status::getStatusName($leaveRequest->status);

        if ($leaveRequest->leave_type_id == 1) {
            return [
                'leave_id' => $leaveRequest->leave_id,
                'name' => $leaveRequest->fullname,
                'position' => $leaveRequest->jawatan,
                'type' => $leaveRequest->jenis_leave,
                'start_date' => $leaveRequest->date_mula,
                'end_date' => $leaveRequest->date_tamat,
                'days_count' => $leaveRequest->totalday . ' Hari',
                'application_date' => $leaveRequest->tarikh_mohon,
                'status' => $statusText, 
            ];
        } elseif ($leaveRequest->leave_type_id == 2) {
            return [
                'leave_id' => $leaveRequest->leave_id,
                'name' => $leaveRequest->fullname,
                'position' => $leaveRequest->jawatan,
                'type' => $leaveRequest->jenis_leave,
                'date' => $leaveRequest->date_mula,
                'day' => $leaveRequest->day_timeoff,
                'start_time' => $leaveRequest->start_time,
                'end_time' => $leaveRequest->end_time,
                'hours_count' => $leaveRequest->total_hours_minutes,
                'application_date' => $leaveRequest->tarikh_mohon,
                'status' => $statusText, 
            ];
        }
        return [];
    });
}
    


    /**
     * Update approval statuses for leave requests in batch.
     *
     * @param int $userId
     * @param array $leaveIds
     * @param int $approvalStatus
     * @param string|null $approvalNotes
     * @return bool
     */
    public function updateApprovalStatusForRequests(int $userId, array $leaveIds, int $approvalStatus, ?string $approvalNotes): bool
    {
        $updateData = [
            'pelulus_id' => $userId,
            'status_pelulus' => $approvalStatus,
            'catatan_pelulus' => $approvalNotes,
            'tkh_kelulusan' => Carbon::now(),
            'status' => $approvalStatus,
            'pengguna' => $userId,
        ];

        return OfficeLeaveRequest::whereIn('id', $leaveIds)
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
    public function getDepartmentByUserId(int $userId): ?string
    {
        return DB::table('users')
            ->where('id', $userId)
            ->where('is_deleted', '!=', 1) // Ensure soft delete handling
            ->value('department_id');
    }


    public function getSupervisedApprovalStatuses(array $filters, String $departmentId): array
    {
        $query = DB::table('office_leave_requests as olr')
            ->leftJoin('users as u', 'olr.idpeg', '=', 'u.id')
            ->leftJoin('leave_types as lt', 'olr.leave_type_id', '=', 'lt.id')
            ->leftJoin('status as s', 'olr.status', '=', 's.id')
            ->select(
                'olr.id',
                'u.fullname as nama_pegawai',
                'u.jawatan as jawatan',
                'lt.diskripsi as jenis_leave',
                
                
                DB::raw("DATE_FORMAT(olr.date_mula, '%d/%m/%Y') as tarikh"),
    
               
                DB::raw("CASE 
                            WHEN olr.leave_type_id = 1 THEN CONCAT(olr.totalday, ' Hari')
                            ELSE CONCAT(olr.start_time, ' - ', olr.end_time) 
                         END as tempoh"),
                         
                'olr.start_time',
                'olr.end_time',
                DB::raw("CONCAT(FLOOR(olr.totalhours), ' Jam ', ROUND((MOD(olr.totalhours, 1) * 60)), ' Minit') AS total_hours_minutes"),
                'olr.reason',
                's.diskripsi as disk_status',
                DB::raw("DATE_FORMAT(olr.tkh_kelulusan, '%d/%m/%Y %h:%i:%s %p') as tarikh_kelulusan")
            )
            ->where('u.department_id', $departmentId)
            ->where('olr.is_deleted', '!=', 1);
    
        // Apply filters for specific pegawai (staff)
        if (!empty($filters['pegawai_id'])) {
            $query->where('olr.idpeg', $filters['pegawai_id']);
        }
    
        if (!empty($filters['month_start']) && !empty($filters['month_end'])) {
            $query->whereBetween('olr.date_mula', [
                Carbon::parse("{$filters['month_start']}-01"),
                Carbon::parse("{$filters['month_end']}-01")->endOfMonth(),
            ]);
        }
    
        return $query->orderBy('olr.date_mula', 'desc')->get()->toArray();
    }
    
}

<?php

namespace App\Repositories;

use App\Models\OfficeLeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OfficeLeaveStatusRepository
{
    /**
     * Get department ID for a given user.
     *
     * @param int $userId
     * @return int|null
     */
    public function getDepartmentIdByUserId(int $userId): ?int
    {
        return DB::table('users')->where('id', $userId)->value('department_id');
    }

      /**
     * Fetch leave statuses based on filters and user ID.
     *
     * @param array $filters
     * @param int $userId
     * @return array
     */
    public function getLeaveStatuses(array $filters, int $userId): array
    {
        $query = DB::table('office_leave_requests as olr')
            ->leftJoin('users as u', 'olr.created_by', '=', 'u.id')
            ->leftJoin('leave_types as lt', 'olr.leave_type_id', '=', 'lt.id')
            ->leftJoin('users as approver', 'olr.approved_by', '=', 'approver.id')
            ->select(
                'olr.id',
                'u.name as nama_pegawai',
                'u.position as jawatan',
                'lt.name as jenis_leave',
                DB::raw("DATE_FORMAT(olr.date, '%d/%m/%Y') as tarikh_mula"),
                DB::raw("CASE
                            WHEN olr.leave_type_id = 1 THEN DATE_FORMAT(olr.date, '%d/%m/%Y')
                            ELSE DATE_FORMAT(olr.date, '%d/%m/%Y')
                        END as tarikh_tamat"),
                'olr.day as hari_timeoff',
                'olr.start_time',
                'olr.end_time',
                DB::raw("FLOOR(TIMESTAMPDIFF(MINUTE, olr.start_time, olr.end_time) / 60) as bilangan_jam"),
                DB::raw("MOD(TIMESTAMPDIFF(MINUTE, olr.start_time, olr.end_time), 60) as minit"),
                'olr.reason',
                DB::raw("DATE_FORMAT(olr.created_at, '%d/%m/%Y %h:%i:%s %p') as tarikh_mohon"),
                'approver.name as nama_pengesah',
                'olr.approval_notes as catatan_pelulus',
                'olr.status as status_code',
                DB::raw("CASE
                        WHEN olr.status = 15 THEN 'Baru'
                        WHEN olr.status = 16 THEN 'Diluluskan'
                        WHEN olr.status = 17 THEN 'Tidak Diluluskan'
                        ELSE 'N/A'
                    END as disk_status"),
                DB::raw("DATE_FORMAT(olr.approval_date, '%d/%m/%Y %h:%i:%s %p') as tarikh_kelulusan")
            )
            ->where('olr.created_by', $userId)
            ->whereNull('olr.deleted_at');

        // Apply date filters
        if (!empty($filters['month_start']) && !empty($filters['month_end'])) {
            $query->whereBetween('olr.date', [
                Carbon::parse("{$filters['month_start']}-01"),
                Carbon::parse("{$filters['month_end']}-01")->endOfMonth(),
            ]);
        }

        return $query->orderBy('olr.date', 'desc')->get()->map(function ($leave) {
            return [
                'nama_pegawai' => $leave->nama_pegawai,
                'jawatan' => $leave->jawatan,
                'jenis_leave' => $leave->jenis_leave,
                'tarikh_mula' => $leave->tarikh_mula,
                'tarikh_tamat' => $leave->tarikh_tamat,
                'hari_timeoff' => $leave->hari_timeoff,
                'masa_mula' => $leave->start_time ? Carbon::parse($leave->start_time)->format('h:i A') : '',
                'masa_tamat' => $leave->end_time ? Carbon::parse($leave->end_time)->format('h:i A') : '',
                'bilangan_jam' => "{$leave->bilangan_jam} Jam {$leave->minit} Minit",
                'reason' => $leave->reason,
                'tarikh_mohon' => $leave->tarikh_mohon,
                'nama_pengesah' => $leave->nama_pengesah,
                'catatan_pelulus' => $leave->catatan_pelulus,
                'status' => $leave->disk_status,
                'tarikh_kelulusan' => $leave->tarikh_kelulusan,
            ];
        })->toArray();
    }


    /**
     * Get the status style based on leave status.
     *
     * @param int $status
     * @return string
     */
    private function getStatusStyle(int $status): string
    {
        if ($status == 15) {
            return 'style="color:#ffc107"'; // Baru
        } elseif ($status == 16) {
            return 'style="color:#28a745"'; // Disahkan
        } elseif ($status == 17) {
            return 'style="color:#ffc107"'; // Tidak Disahkan
        }
        return '';
    }
}

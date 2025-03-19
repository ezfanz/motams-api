<?php

namespace App\Repositories;

use App\Models\OfficeLeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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
        return DB::table('users')
            ->where('id', $userId)
            ->where('is_deleted', '!=', 1) // Ensure soft delete handling
            ->value('department_id');
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
            ->leftJoin('users as u', 'olr.idpeg', '=', 'u.id')
            ->leftJoin('leave_types as lt', 'olr.leave_type_id', '=', 'lt.id')
            ->select(
                'olr.id',
                'u.fullname as nama_pegawai',
                'u.jawatan',
                'lt.diskripsi as jenis_leave',
                DB::raw("DATE_FORMAT(olr.date_mula, '%d/%m/%Y') as tarikh_mula"),
                DB::raw("DATE_FORMAT(olr.date_tamat, '%d/%m/%Y') as tarikh_tamat"),
                'olr.day_timeoff',
                'olr.start_time',
                'olr.end_time',
                'olr.totalday',
                'olr.totalhours',
                'olr.reason',
                'olr.catatan_pelulus as catatan_pelulus',
                DB::raw("
                CONCAT(
                    FLOOR(olr.totalhours), ' Jam ', 
                    ROUND((MOD(olr.totalhours, 1) * 60)), ' Minit'
                ) as total_hours_minutes
            "),
                DB::raw("DATE_FORMAT(olr.tkh_mohon, '%d/%m/%Y %h:%i:%s %p') as tarikh_mohon"),
                DB::raw("DATE_FORMAT(olr.tkh_kelulusan, '%d/%m/%Y %h:%i:%s %p') as tarikh_kelulusan"),
                'olr.status',
                DB::raw("CASE
                WHEN olr.status = 15 THEN 'Baru'
                WHEN olr.status = 16 THEN 'Diluluskan'
                WHEN olr.status = 17 THEN 'Tidak Diluluskan'
                ELSE 'N/A'
            END as disk_status")
            );

        // Debug Log
        Log::info("Filters Applied", [
            'userId' => $userId,
            'month_start' => $filters['month_start'] ?? null,
            'month_end' => $filters['month_end'] ?? null,
        ]);

        // Remove idpeg filter temporarily
        if (!empty($userId)) {
            $query->where('olr.idpeg', $userId);
        }

        // Debug Date Filter
        if (!empty($filters['month_start']) && !empty($filters['month_end'])) {
            $startDate = Carbon::parse("{$filters['month_start']}-01")->startOfMonth();
            $endDate = Carbon::parse("{$filters['month_end']}-01")->endOfMonth();

            Log::info("Date Range Applied", [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $query->whereBetween('olr.date_mula', [$startDate, $endDate]);
        }

        // Debug Query Execution
        Log::info("Executing Query: " . $query->toSql(), $query->getBindings());

        return $query->orderBy('olr.date_mula', 'desc')->get()->map(function ($leave) {
            return [
                'id' => $leave->id,
                'nama_pegawai' => $leave->nama_pegawai,
                'jawatan' => $leave->jawatan,
                'jenis_leave' => $leave->jenis_leave,
                'tarikh_mula' => $leave->tarikh_mula,
                'tarikh_tamat' => $leave->tarikh_tamat,
                'hari_timeoff' => $leave->day_timeoff,
                'masa_mula' => $leave->start_time ? Carbon::parse($leave->start_time)->format('h:i A') : '',
                'masa_tamat' => $leave->end_time ? Carbon::parse($leave->end_time)->format('h:i A') : '',
                'bilangan_jam' => $leave->total_hours_minutes,
                'reason' => $leave->reason,
                'tarikh_mohon' => $leave->tarikh_mohon,
                'status' => $leave->disk_status,
                'tarikh_kelulusan' => $leave->tarikh_kelulusan ? $leave->tarikh_kelulusan : 'Belum Diluluskan',
                'catatan_pelulus' => $leave->catatan_pelulus ? $leave->catatan_pelulus : 'Tiada Catatan Dari pelulus',
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

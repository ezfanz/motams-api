<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class AttendanceStatusRepository
{
    public function getStatusListByDateRange(string $startDay, string $endDay)
    {
        // Query to fetch data based on date range
        return DB::table('reason_transactions')
            ->select(
                'reason_transactions.id',
                'reason_transactions.employee_id',
                'users.name',
                'users.position',
                'reason_transactions.log_timestamp',
                'reason_transactions.reason_type_id',
                'reason_transactions.approval_notes',
                'reason_transactions.status',
                'reason_types.description as disk_jenisalasan',
                'reasons.description as disk_alasan'
            )
            ->leftJoin('users', 'reason_transactions.employee_id', '=', 'users.id')
            ->leftJoin('reasons', 'reason_transactions.reason_id', '=', 'reasons.id')
            ->leftJoin('reason_types', 'reason_transactions.reason_type_id', '=', 'reason_types.id')
            ->whereNull('reason_transactions.deleted_at')
            ->whereBetween('reason_transactions.log_timestamp', [$startDay, $endDay])
            ->orderBy('reason_transactions.log_timestamp', 'desc')
            ->get();
    }
}

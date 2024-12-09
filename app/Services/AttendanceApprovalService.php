<?php

namespace App\Services;

use App\Models\ReasonTransaction;
use Illuminate\Support\Facades\DB;

class AttendanceApprovalService
{
    public function fetchApprovalList(int $userId, int $roleId, int $dayNow, string $currentMonth, string $lastMonth)
    {
        $startDay = $dayNow > 10
            ? now()->startOfMonth()->toDateTimeString()
            : now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();

        $endDay = now()->endOfMonth()->toDateTimeString();

        // If Admin
        if ($roleId == 3) {
            $approvals = ReasonTransaction::select(
                'reason_transactions.id',
                'reason_transactions.employee_id as idpeg',
                'users.name',
                'users.position',
                'reason_transactions.log_timestamp as log_datetime',
                'reason_transactions.reason_type_id as jenisalasan_id',
                'reason_transactions.employee_notes as catatan_peg',
                'reason_types.description as disk_jenisalasan',
                'reasons.description as disk_alasan'
            )
                ->leftJoin('users', 'reason_transactions.employee_id', '=', 'users.id')
                ->leftJoin('reasons', 'reason_transactions.reason_id', '=', 'reasons.id')
                ->leftJoin('reason_types', 'reason_transactions.reason_type_id', '=', 'reason_types.id')
                ->whereNull('reason_transactions.deleted_at')
                ->where('reason_transactions.status', 2) // Pending approval
                ->whereBetween('reason_transactions.log_timestamp', [$startDay, $endDay])
                ->orderBy('reason_transactions.log_timestamp', 'DESC')
                ->get();
        } else {
            // If Reviewer
            $approvals = ReasonTransaction::select(
                'reason_transactions.id',
                'reason_transactions.employee_id as idpeg',
                'users.name',
                'users.position',
                'reason_transactions.log_timestamp as log_datetime',
                'reason_transactions.reason_type_id as jenisalasan_id',
                'reason_transactions.employee_notes as catatan_peg',
                'reason_types.description as disk_jenisalasan',
                'reasons.description as disk_alasan'
            )
                ->leftJoin('users', 'reason_transactions.employee_id', '=', 'users.id')
                ->leftJoin('reasons', 'reason_transactions.reason_id', '=', 'reasons.id')
                ->leftJoin('reason_types', 'reason_transactions.reason_type_id', '=', 'reason_types.id')
                ->whereNull('reason_transactions.deleted_at')
                ->where('reason_transactions.status', 2) // Pending approval
                ->where('users.pengesah_id', $userId) // Filter by the reviewer
                ->whereBetween('reason_transactions.log_timestamp', [$startDay, $endDay])
                ->orderBy('reason_transactions.log_timestamp', 'DESC')
                ->get();
        }

        // Format the data for display
        return $approvals->map(function ($approval) {
            return [
                'name' => $approval->name,
                'position' => $approval->position,
                'date' => date('d/m/Y', strtotime($approval->log_datetime)),
                'day' => \Carbon\Carbon::parse($approval->log_datetime)->isoFormat('dddd'),
                'time' => date('h:i:s A', strtotime($approval->log_datetime)),
                'reason' => $approval->disk_alasan,
                'type' => $this->getReasonType($approval->jenisalasan_id),
            ];
        });
    }

    private function getReasonType(int $jenisalasanId)
    {
        return match ($jenisalasanId) {
            1 => 'Lewat',
            2 => 'Balik Awal',
            3 => 'Tidak Hadir',
            default => 'Lain-lain',
        };
    }
}

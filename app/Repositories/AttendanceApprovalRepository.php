<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceApprovalRepository
{
    public function fetchApprovalList(int $userId, int $roleId, string $monthSearch)
    {
        $firstDayOfMonth = Carbon::parse($monthSearch)->firstOfMonth()->toDateTimeString();
        $lastDayOfMonth = Carbon::parse($monthSearch)->lastOfMonth()->toDateTimeString();

        $query = DB::table('reason_transactions')
            ->select(
                'reason_transactions.id',
                'reason_transactions.employee_id',
                'users.name',
                'users.position',
                'reason_transactions.log_timestamp',
                'reason_transactions.reason_type_id',
                'reason_transactions.review_notes',
                'reason_transactions.status',
                'reason_types.description AS disk_jenisalasan',
                'reasons.description AS disk_alasan'
            )
            ->leftJoin('users', 'reason_transactions.employee_id', '=', 'users.id')
            ->leftJoin('reasons', 'reason_transactions.reason_id', '=', 'reasons.id')
            ->leftJoin('reason_types', 'reason_transactions.reason_type_id', '=', 'reason_types.id')
            ->whereNull('reason_transactions.deleted_at')
            ->whereBetween('reason_transactions.log_timestamp', [$firstDayOfMonth, $lastDayOfMonth])
            ->orderBy('reason_transactions.log_timestamp', 'DESC');

        if ($roleId == 3) {
            // Admin: No additional filters
        } else {
            $query->where('users.approving_officer_id', $userId);
        }

        return $query->get()->map(function ($record) {
            return [
                'name' => $record->fullname,
                'position' => $record->jawatan,
                'date' => date('d/m/Y', strtotime($record->log_datetime)),
                'day' => Carbon::parse($record->log_datetime)->isoFormat('dddd'),
                'time' => date('h:i:s A', strtotime($record->log_datetime)),
                'reason' => $record->disk_alasan,
                'type' => $this->getReasonType($record->jenisalasan_id),
                'statusColor' => $this->getStatusColor($record->status),
                'statusText' => $this->getStatusText($record->status),
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

    private function getStatusColor(int $status)
    {
        return match ($status) {
            4 => '#28a745', // Green
            2 => '#17a2b8', // Blue
            1, 3, 5 => '#ffc107', // Yellow
            default => '#dc3545', // Red
        };
    }

    private function getStatusText(int $status)
    {
        return match ($status) {
            4 => 'Alasan Diterima Pengesah',
            2 => 'Alasan Diterima Penyemak',
            1, 3, 5 => 'Menunggu Semakan/ Alasan Tidak Diterima/ Memerlukan Maklumat Lanjut',
            default => 'Tidak Valid',
        };
    }
}

<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Status;

class AttendanceApprovalRepository
{
    public function fetchApprovalList(int $userId, int $roleId, ?int $monthSearch = null, ?int $yearSearch = null)
    {
        $query = DB::table('trans_alasan')
            ->select(
                'trans_alasan.id',
                'trans_alasan.idpeg',
                'users.fullname',
                'users.jawatan AS position',
                'trans_alasan.log_datetime',
                'trans_alasan.jenisalasan_id',
                'trans_alasan.catatan_peg AS reason_note',
                'trans_alasan.status',
                'jenis_alasan.diskripsi_bm AS reason_type',
                'alasan.diskripsi AS reason'
            )
            ->leftJoin('users', 'trans_alasan.idpeg', '=', 'users.id')
            ->leftJoin('alasan', 'trans_alasan.alasan_id', '=', 'alasan.id')
            ->leftJoin('jenis_alasan', 'trans_alasan.jenisalasan_id', '=', 'jenis_alasan.id')
            ->where('trans_alasan.is_deleted', '!=', 1);
    
        // Role-based filtering
        if ($roleId != 3) { // Non-admin roles
            $query->where('users.pengesah_id', $userId);
        }
    
        // Status filter (only fetch "Pending Approval")
        $query->where('trans_alasan.status', Status::DITERIMA_PENYEMAK);
    
        // Month and year filtering
        if ($monthSearch && $yearSearch) {
            $firstDayOfMonth = Carbon::createFromDate($yearSearch, $monthSearch, 1)->startOfMonth()->toDateTimeString();
            $lastDayOfMonth = Carbon::createFromDate($yearSearch, $monthSearch, 1)->endOfMonth()->toDateTimeString();
            $query->whereBetween('trans_alasan.log_datetime', [$firstDayOfMonth, $lastDayOfMonth]);
        } else {
            // Default to current month if no filters provided
            $firstDayOfCurrentMonth = now()->startOfMonth()->toDateTimeString();
            $lastDayOfCurrentMonth = now()->endOfMonth()->toDateTimeString();
            $query->whereBetween('trans_alasan.log_datetime', [$firstDayOfCurrentMonth, $lastDayOfCurrentMonth]);
        }
    
        $query->orderBy('trans_alasan.log_datetime', 'DESC');
    
        // Map results for UI
        return $query->get()->map(function ($record) {
            $record->trdate = date('d/m/Y', strtotime($record->log_datetime));
            $record->masa = date('h:i:s A', strtotime($record->log_datetime));
            $record->hari = Carbon::parse($record->log_datetime)->isoFormat('dddd');
    
            $displayData = [
                'name' => $record->fullname,
                'position' => $record->position,
                'date' => $record->trdate,
                'day' => $record->hari,
                'reason' => $record->reason,
                'statusText' => Status::getStatusName($record->status),
                'boxColor' => Status::getStatusColor($record->status),
            ];
    
            // Add type-specific fields
            if ($record->jenisalasan_id == 1) { // Jika Lewat
                $displayData['time'] = $record->masa;
                $displayData['type'] = 'Lewat';
            } elseif ($record->jenisalasan_id == 2) { // Jika Balik Awal
                $displayData['time'] = $record->masa;
                $displayData['type'] = 'Balik Awal';
            } elseif ($record->jenisalasan_id == 3) { // Jika Tidak Hadir
                $displayData['type'] = 'Tidak Hadir';
            }
    
            return $displayData;
        })->toArray();
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

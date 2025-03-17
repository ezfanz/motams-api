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

    public function getFilteredRecords(array $filters)
    {
        $userId = $filters['user_id'];
        $roleId = $filters['role_id'];
        $status = $filters['status'] ?? null;
        $months = is_array($filters['month']) ? $filters['month'] : [$filters['month']];
        $year = $filters['year'] ?? null;

        // Get start and last day of the current month
        $monthnow = Carbon::now()->format('Y-m');
        $datenow = Carbon::now()->format('Y-m-d');
        $daynow = Carbon::now()->format('d');
        
        $firstDayofCurrentMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $firstDayofPreviousMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastDayofCurrentMonth = Carbon::now()->endOfMonth()->toDateTimeString();
        
        $startDay = $daynow > 10 ? $firstDayofCurrentMonth : $firstDayofPreviousMonth;
        

        $query = DB::table('trans_alasan')
            ->select(
                'trans_alasan.id AS tralasan_id',
                'trans_alasan.idpeg AS user_id',
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
            ->where('trans_alasan.is_deleted', '!=', 1)
            ->where('trans_alasan.status', Status::DITERIMA_PENYEMAK) // status
        ->whereBetween('trans_alasan.log_datetime', [$startDay, $lastDayofCurrentMonth]); // Filter by date range

        // Role-based filtering
        if ($roleId != 3) { // Non-admin roles
            $query->where('users.penyemak_id', $userId);
        }

        // Status filtering (optional)
        if (!empty($status)) {
            $query->where('trans_alasan.status', $status);
        }

        // Month filtering for both previous and current months
        if (!empty($year) && !empty($months)) {
            $query->whereYear('trans_alasan.log_datetime', $year)
                ->whereIn(DB::raw('MONTH(trans_alasan.log_datetime)'), $months);
        }

        $query->orderBy('trans_alasan.log_datetime', direction: 'DESC');

        return $query->get()->map(function ($record) {
            return [
                'tralasan_id' => $record->tralasan_id,
                'name' => $record->fullname,
                'position' => $record->position,
                'date' => date('d/m/Y', strtotime($record->log_datetime)),
                'day' => Carbon::parse($record->log_datetime)->isoFormat('dddd'),
                'time' => date('h:i:s A', strtotime($record->log_datetime)),
                'reason' => $record->reason,
                'type' => $this->getReasonType($record->jenisalasan_id),
                'statusColor' => Status::getStatusColor($record->status),
                'statusText' => Status::getStatusName($record->status),
            ];
        })->toArray();
    }


    private function getReasonType(int $reasonTypeId)
    {
        return match ($reasonTypeId) {
            1 => 'Lewat',
            2 => 'Balik Awal',
            3 => 'Tidak Hadir',
            default => 'Lain-lain',
        };
    }
}

<?php

namespace App\Services;

use App\Repositories\AttendanceStatusRepository;

class AttendanceStatusService
{
    protected $repository;

    public function __construct(AttendanceStatusRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetchStatusList(string $month)
    {
        // Get the first and last day of the month
        $firstDay = now()->parse($month)->firstOfMonth()->toDateTimeString();
        $lastDay = now()->parse($month)->lastOfMonth()->toDateTimeString();

        // Fetch the records from the repository
        $records = $this->repository->getStatusListByDateRange($firstDay, $lastDay);

        // Format the data for the response
        return $records->map(function ($record) {
            return [
                'name' => $record->fullname,
                'position' => $record->jawatan,
                'date' => date('d/m/Y', strtotime($record->log_datetime)),
                'day' => \Carbon\Carbon::parse($record->log_datetime)->isoFormat('dddd'),
                'time' => date('h:i:s A', strtotime($record->log_datetime)),
                'reason' => $record->disk_alasan,
                'type' => $this->getReasonType($record->jenisalasan_id),
                'statusText' => $this->getStatusText($record->status),
                'statusColor' => $this->getStatusColor($record->status),
            ];
        });
    }

    private function getReasonType(int $jenisalasanId): string
    {
        return match ($jenisalasanId) {
            1 => 'Lewat',
            2 => 'Balik Awal',
            3 => 'Tidak Hadir',
            default => 'Lain-lain',
        };
    }

    private function getStatusText(int $status): string
    {
        return match ($status) {
            4 => 'Alasan Diterima Pengesah',
            2 => 'Alasan Diterima Penyemak',
            1, 3, 5 => 'Menunggu Semakan',
            default => 'Tidak Diterima',
        };
    }

    private function getStatusColor(int $status): string
    {
        return match ($status) {
            4 => '#28a745', // Green
            2 => '#17a2b8', // Blue
            1, 3, 5 => '#ffc107', // Yellow
            default => '#dc3545', // Red
        };
    }
}

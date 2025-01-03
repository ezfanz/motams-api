<?php

namespace App\Repositories;

use App\Models\AttendanceRecord;
use App\Models\ReviewStatus;
use App\Models\ReasonTransaction;
use App\Models\TransAlasan;


class VerificationRepository
{
    public function filterByStatusAndDate(array $filters)
    {
        $query = AttendanceRecord::with(['user', 'verificationStatus'])
            ->whereNull('verification_status_id'); // Only fetch records ready for verification

        if (isset($filters['month']) && isset($filters['year'])) {
            $query->whereMonth('date', $filters['month'])
                ->whereYear('date', $filters['year']);
        }

        return $query->paginate(10);
    }

    public function getStatusOptions()
    {
        return ReviewStatus::all();
    }

    public function updateBatchVerificationStatus(array $recordIds, $statusId, $notes)
    {
        return AttendanceRecord::whereIn('id', $recordIds)
            ->update(['verification_status_id' => $statusId, 'verification_notes' => $notes]);
    }

    public function getMonthlyVerificationSummary($month, $year)
    {
        return AttendanceRecord::with(['verificationStatus', 'employee'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('verification_status_id')
            ->map(function ($records, $statusId) {
                return [
                    'status' => $records->first()->verificationStatus->status ?? 'No Status',
                    'total' => $records->count(),
                    'records' => $records->map(function ($record) {
                        return [
                            'employee_name' => $record->employee->name,
                            'date' => $record->date,
                            'reason' => $record->reason,
                            'status' => $record->verificationStatus->status ?? 'No Status',
                        ];
                    })
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Find multiple transactions by IDs.
     */
    public function findByIds(array $ids)
    {
        return TransAlasan::whereIn('id', $ids)
        ->where('is_deleted', '!=', 1)
        ->get();
    }
}

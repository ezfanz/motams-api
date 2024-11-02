<?php

namespace App\Repositories;

use App\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;


class AttendanceRecordRepository
{
    /**
     * Get all attendance records.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return AttendanceRecord::with(['status', 'createdByUser', 'details'])->get();
    }

    /**
     * Find an attendance record by ID.
     *
     * @param int $id
     * @return \App\Models\AttendanceRecord
     */
    public function find($id)
    {
        return AttendanceRecord::with(['status', 'createdByUser', 'details'])->findOrFail($id);
    }

    /**
     * Create a new attendance record.
     *
     * @param array $data
     * @return \App\Models\AttendanceRecord
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id(); // Set created_by from the session
        return AttendanceRecord::create($data);
    }

    /**
     * Update an existing attendance record by ID.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\AttendanceRecord
     */
    public function update($id, array $data)
    {
        $attendanceRecord = $this->find($id);
        $attendanceRecord->update($data);

        return $attendanceRecord;
    }

    /**
     * Delete an attendance record by ID.
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $attendanceRecord = $this->find($id);
        $attendanceRecord->delete();
    }

    public function getByStatus(int $statusId)
    {
        return AttendanceRecord::with(['attendanceStatus', 'createdByUser', 'details'])
            ->where('status_id', $statusId)
            ->get();
    }

     /**
     * Filter attendance records by status and date.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function filterByStatusAndDate(array $filters)
    {
        $query = AttendanceRecord::with(['reviewStatus', 'employee']);

        if (isset($filters['status'])) {
            $query->whereHas('reviewStatus', function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        }

        if (isset($filters['month']) && isset($filters['year'])) {
            $query->whereMonth('date', $filters['month'])
                  ->whereYear('date', $filters['year']);
        }

        return $query->get();
    }

    /**
     * Batch update review status for multiple attendance records.
     *
     * @param array $recordIds
     * @param int $reviewStatusId
     * @param string|null $reviewNotes
     * @return int
     */
    public function updateBatchReviewStatus(array $recordIds, int $reviewStatusId, ?string $reviewNotes)
    {
        return AttendanceRecord::whereIn('id', $recordIds)->update([
            'review_status_id' => $reviewStatusId,
            'review_notes' => $reviewNotes,
            'updated_at' => now(),
        ]);
    }

  /**
 * Get the count and list of attendance records by review status for a specific month and year.
 *
 * @param int $month
 * @param int $year
 * @return array
 */
public function getMonthlyStatusCounts(int $month, int $year)
{
    return AttendanceRecord::with('reviewStatus')
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->get()
        ->groupBy('review_status_id')
        ->map(function ($records, $statusId) {
            return [
                'status' => optional($records->first()->reviewStatus)->status ?? 'No Status',
                'total' => $records->count(),
                'records' => $records->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'day' => $record->day,
                        'reason' => $record->reason,
                        'check_in_time' => $record->check_in_time,
                        'check_out_time' => $record->check_out_time,
                        'review_notes' => $record->review_notes,
                    ];
                })->toArray(),
            ];
        })
        ->values()
        ->toArray();
}
}

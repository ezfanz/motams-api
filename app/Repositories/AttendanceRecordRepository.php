<?php

namespace App\Repositories;

use App\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ReasonTransaction;


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

    public function getAttendanceRecordsWithDetails($userId, $staffId, $startDay, $lastDay)
    {
        // Log debug information
        Log::info('Fetching attendance records', [
            'userId' => $userId,
            'staffId' => $staffId,
            'startDay' => $startDay,
            'lastDay' => $lastDay,
        ]);

        // Perform the query
        $results = DB::table('calendars')
            ->leftJoin('transit', function ($join) use ($staffId) {
                $join->on(DB::raw('DATE(calendars.fulldate)'), '=', DB::raw('DATE(transit.trdate)'))
                    ->where('transit.staffid', '=', (int) $staffId); // Ensure staffId is cast to integer
            })
            ->selectRaw('
            calendars.fulldate,
            calendars.year,
            calendars.monthname,
            calendars.dayname,
            calendars.isweekday,
            calendars.isholiday,
            transit.staffid,
            ? AS idpeg,
            MIN(transit.trdatetime) AS datetimein,
            DATE_FORMAT(MIN(transit.trdatetime), "%T") AS timein,
            MAX(transit.trdatetime) AS datetimeout,
            DATE_FORMAT(MAX(transit.trdatetime), "%T") AS timeout,
            CASE
                WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND TIME(MIN(transit.trdatetime)) >= "09:01:00" THEN 1
                ELSE 0
            END AS latein,
            CASE
                WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND transit.ramadhan_yt = 0 AND TIME(MAX(transit.trdatetime)) <= "18:00:00"
                     AND (HOUR(TIMESTAMPADD(MINUTE, 540, MIN(transit.trdatetime))) * 60 + MINUTE(TIMESTAMPADD(MINUTE, 540, MIN(transit.trdatetime)))) > (HOUR(MAX(transit.trdatetime)) * 60 + MINUTE(MAX(transit.trdatetime)))
                THEN 1
                ELSE 0
            END AS earlyout,
            (
                SELECT reasons.description
                FROM reason_transactions
                JOIN reasons ON reason_transactions.reason_id = reasons.id
                WHERE reason_transactions.log_timestamp = MIN(transit.trdatetime)
                AND reason_transactions.employee_id = ?
                AND reason_transactions.reason_type_id = 1
                AND reason_transactions.deleted_at IS NULL
                LIMIT 1
            ) AS latereason,
            (
                SELECT reasons.description
                FROM reason_transactions
                JOIN reasons ON reason_transactions.reason_id = reasons.id
                WHERE reason_transactions.log_timestamp = MAX(transit.trdatetime)
                AND reason_transactions.employee_id = ?
                AND reason_transactions.reason_type_id = 2
                AND reason_transactions.deleted_at IS NULL
                LIMIT 1
            ) AS earlyreason,
            (
                SELECT reasons.description
                FROM reason_transactions
                JOIN reasons ON reason_transactions.reason_id = reasons.id
                WHERE reason_transactions.log_timestamp = calendars.fulldate
                AND reason_transactions.employee_id = ?
                AND reason_transactions.reason_type_id = 3
                AND reason_transactions.deleted_at IS NULL
                LIMIT 1
            ) AS absentreasont,
            (
                SELECT reason_transactions.status
                FROM reason_transactions
                WHERE reason_transactions.log_timestamp = MIN(transit.trdatetime)
                AND reason_transactions.employee_id = ?
                AND reason_transactions.reason_type_id = 1
                AND reason_transactions.deleted_at IS NULL
                LIMIT 1
            ) AS statuslate,
            (
                SELECT reason_transactions.status
                FROM reason_transactions
                WHERE reason_transactions.log_timestamp = MAX(transit.trdatetime)
                AND reason_transactions.employee_id = ?
                AND reason_transactions.reason_type_id = 2
                AND reason_transactions.deleted_at IS NULL
                LIMIT 1
            ) AS statusearly,
            (
                SELECT reason_transactions.status
                FROM reason_transactions
                WHERE reason_transactions.log_timestamp = calendars.fulldate
                AND reason_transactions.employee_id = ?
                AND reason_transactions.reason_type_id = 3
                AND reason_transactions.deleted_at IS NULL
                LIMIT 1
            ) AS statusabsent
        ', [
                $userId, // idpeg
                $userId,
                $userId,
                $userId, // For reasons
                $userId,
                $userId,
                $userId, // For statuses
            ])
            ->whereBetween('calendars.fulldate', [$startDay, $lastDay])
            ->where('calendars.isweekday', 1)
            ->where('calendars.isholiday', 0)
            ->groupBy(
                'calendars.fulldate',
                'calendars.year',
                'calendars.monthname',
                'calendars.dayname',
                'calendars.isweekday',
                'calendars.isholiday',
                'transit.staffid'
            )
            ->orderBy('calendars.fulldate', 'ASC')
            ->get();

        // Log the executed query
        Log::debug('Executed Attendance Query', DB::getQueryLog());

        return $results;
    }
    public function fetchLateAttendanceRecords(int $userId, string $startDay, string $lastDay): array
    {
        $transitSubquery = DB::table('transit')
            ->select('staffid', DB::raw('DATE(trdate) as trdate'), DB::raw('MIN(trdatetime) as min_trdatetime'))
            ->where('staffid', '=', $userId)
            ->whereBetween(DB::raw('DATE(trdate)'), [$startDay, $lastDay])
            ->groupBy('staffid', DB::raw('DATE(trdate)'));

        $query = DB::table('calendars')
            ->leftJoinSub($transitSubquery, 'transit_min', function ($join) {
                $join->on(DB::raw('DATE(calendars.fulldate)'), '=', 'transit_min.trdate');
            })
            ->leftJoin('reason_transactions', function ($join) use ($userId) {
                $join->on('reason_transactions.log_timestamp', '=', 'transit_min.min_trdatetime')
                    ->where('reason_transactions.employee_id', '=', $userId)
                    ->where('reason_transactions.reason_type_id', '=', 1)
                    ->whereNull('reason_transactions.deleted_at');
            })
            ->leftJoin('reasons', 'reason_transactions.reason_id', '=', 'reasons.id')
            ->select(
                'calendars.fulldate',
                DB::raw('YEAR(calendars.fulldate) AS year'),
                DB::raw('MONTHNAME(calendars.fulldate) AS monthname'),
                DB::raw('DAYNAME(calendars.fulldate) AS dayname'),
                'calendars.isweekday',
                'calendars.isholiday',
                'transit_min.staffid',
                DB::raw("$userId AS idpeg"),
                'transit_min.min_trdatetime AS datetimein',
                DB::raw('DATE_FORMAT(transit_min.min_trdatetime, "%T") AS timein'),
                DB::raw('CASE WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND TIME(transit_min.min_trdatetime) >= "09:01:00" THEN 1 ELSE 0 END AS latein'),
                'reasons.description AS latereason',
                'reason_transactions.status AS statuslate'
            )
            ->whereBetween('calendars.fulldate', [$startDay, $lastDay])
            ->where('calendars.isweekday', 1)
            ->where('calendars.isholiday', 0)
            ->whereNotNull('transit_min.min_trdatetime')
            ->havingRaw('TIME(transit_min.min_trdatetime) >= "09:01:00"')
            ->orderBy('calendars.fulldate', 'ASC');

        $records = $query->get();

        return $records->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->fulldate));
            $record->box_color = $this->determineBoxColor($record->statuslate);
            return $record;
        })->toArray();
    }

    public function fetchAbsentRecords(int $userId, string $startDay, string $lastDay): array
    {
        // Subquery to get absence reasons
        $reasonSubquery = DB::table('reason_transactions')
            ->join('reasons', 'reason_transactions.reason_id', '=', 'reasons.id')
            ->select('reason_transactions.log_timestamp', 'reasons.description AS absentreasont', 'reason_transactions.status AS statusabsent')
            ->where('reason_transactions.employee_id', '=', $userId)
            ->where('reason_transactions.reason_type_id', '=', 3)
            ->whereNull('reason_transactions.deleted_at');

        // Main query for calendars and absence details
        $query = DB::table('calendars')
            ->leftJoin('transit', function ($join) use ($userId) {
                $join->on(DB::raw('DATE(calendars.fulldate)'), '=', DB::raw('DATE(transit.trdate)'))
                    ->where('transit.staffid', '=', $userId)
                    ->whereNull('transit.trdatetime'); // Null trdatetime indicates absence
            })
            ->leftJoinSub($reasonSubquery, 'reasons_sub', function ($join) {
                $join->on('calendars.fulldate', '=', 'reasons_sub.log_timestamp');
            })
            ->select(
                'calendars.fulldate',
                DB::raw('YEAR(calendars.fulldate) AS year'),
                DB::raw('MONTHNAME(calendars.fulldate) AS monthname'),
                DB::raw('DAYNAME(calendars.fulldate) AS dayname'),
                'calendars.isweekday',
                'calendars.isholiday',
                'transit.staffid',
                DB::raw("$userId AS idpeg"),
                'reasons_sub.absentreasont',
                'reasons_sub.statusabsent'
            )
            ->whereBetween('calendars.fulldate', [$startDay, $lastDay])
            ->where('calendars.isweekday', 1)
            ->where('calendars.isholiday', 0)
            ->whereNull('transit.trdatetime') // Filter for absent dates
            ->groupBy('calendars.fulldate', 'calendars.isweekday', 'calendars.isholiday', 'transit.staffid', 'reasons_sub.absentreasont', 'reasons_sub.statusabsent')
            ->orderBy('calendars.fulldate', 'ASC');

        // Fetch records and map additional fields
        $records = $query->get();

        return $records->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->fulldate));
            $record->box_color = $this->determineBoxColor($record->statusabsent);
            return $record;
        })->toArray();
    }

    public function fetchEarlyLeaveRecords(int $userId, string $startDay, string $lastDay): array
    {
        // Fetch staff ID from user
        $staffId = User::withoutTrashed()
            ->where('id', $userId)
            ->value('staff_id');

        // Query the `lateinoutview` to fetch early leave records
        $query = DB::table('lateinoutview')
            ->select(
                'lateinoutview.staffid',
                'lateinoutview.day',
                'lateinoutview.trdate',
                'lateinoutview.isweekday',
                'lateinoutview.isholiday',
                'lateinoutview.datetimeout',
                DB::raw("DATE_FORMAT(lateinoutview.datetimeout, '%T') AS timeout"),
                'lateinoutview.earlyout',
                DB::raw("$userId AS idpeg"),
                DB::raw("
                (SELECT reasons.description
                 FROM reason_transactions
                 LEFT JOIN reasons ON reason_transactions.reason_id = reasons.id
                 WHERE reason_transactions.log_timestamp = lateinoutview.datetimeout
                   AND reason_transactions.employee_id = $userId
                   AND reason_transactions.reason_type_id = 2
                   AND reason_transactions.deleted_at IS NULL
                ) AS earlyreason"),
                DB::raw("
                (SELECT reason_transactions.status
                 FROM reason_transactions
                 WHERE reason_transactions.log_timestamp = lateinoutview.datetimeout
                   AND reason_transactions.employee_id = $userId
                   AND reason_transactions.reason_type_id = 2
                   AND reason_transactions.deleted_at IS NULL
                ) AS statusearly")
            )
            ->where('lateinoutview.staffid', $staffId)
            ->whereBetween('lateinoutview.trdate', [$startDay, $lastDay])
            ->where('lateinoutview.earlyout', 1)
            ->where('lateinoutview.isweekday', 1)
            ->where('lateinoutview.isholiday', 0)
            ->orderBy('lateinoutview.trdate', 'ASC');

        // Fetch records
        $records = $query->get();

        // Map and format records
        return $records->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->trdate));
            $record->box_color = $this->determineBoxColor($record->statusearly);
            return $record;
        })->toArray();
    }

    private function determineBoxColor($status)
    {
        if ($status == 4) {
            return '#28a745'; // Green
        } elseif ($status == 2) {
            return '#17a2b8'; // Blue
        } elseif (in_array($status, [1, 3, 5])) {
            return '#ffc107'; // Yellow
        } else {
            return '#dc3545'; // Red
        }
    }


    /**
     * Get review count for admin roles.
     *
     * @param int $dayNow
     * @param string $currentMonth
     * @param string $lastMonth
     * @return int
     */
    public function getAdminReviewCount(int $dayNow, string $currentMonth, string $lastMonth): int
    {
        return $dayNow > 10
            ? ReasonTransaction::whereNull('deleted_at') // Soft delete condition
                ->where('status', 1)
                ->whereMonth('log_timestamp', now()->month)
                ->count()
            : ReasonTransaction::whereNull('deleted_at') // Soft delete condition
                ->where('status', 1)
                ->where(function ($query) use ($currentMonth, $lastMonth) {
                    $query->whereMonth('log_timestamp', now()->month)
                        ->orWhere(function ($query) use ($lastMonth) {
                            $query->whereMonth('log_timestamp', now()->subMonth()->month);
                        });
                })->count();
    }

    /**
     * Get review count for reviewer roles.
     *
     * @param int $userId
     * @param int $dayNow
     * @param string $currentMonth
     * @param string $lastMonth
     * @return int
     */
    public function getReviewerReviewCount(int $userId, int $dayNow, string $currentMonth, string $lastMonth): int
    {
        return $dayNow > 10
            ? ReasonTransaction::leftJoin('users', 'reason_transactions.employee_id', '=', 'users.id')
                ->where('reason_transactions.status', 1)
                ->where('users.reviewing_officer_id', $userId)
                ->where('reason_transactions.is_deleted', '!=', 1)
                ->whereMonth('reason_transactions.log_timestamp', now()->month)
                ->count()
            : ReasonTransaction::leftJoin('users', 'reason_transactions.employee_id', '=', 'users.id')
                ->where('reason_transactions.status', 1)
                ->where('users.reviewing_officer_id', $userId)
                ->where('reason_transactions.is_deleted', '!=', 1)
                ->where(function ($query) use ($currentMonth, $lastMonth) {
                    $query->whereMonth('reason_transactions.log_timestamp', now()->month)
                        ->orWhere(function ($query) use ($lastMonth) {
                            $query->whereMonth('reason_transactions.log_timestamp', now()->subMonth()->month);
                        });
                })->count();
    }



}

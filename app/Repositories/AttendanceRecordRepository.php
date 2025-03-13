<?php

namespace App\Repositories;

use App\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ReasonTransaction;
use App\Models\TransAlasan;
use Carbon\Carbon;
use App\Models\Status;



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
                    ->where('transit.staffid', '=', (int) $staffId);
            })
            ->leftJoin('trans_alasan AS late_alasan', function ($join) use ($userId) {
                $join->on('late_alasan.log_datetime', '=', DB::raw('(SELECT MIN(trdatetime) FROM transit WHERE transit.staffid = late_alasan.idpeg)'))
                    ->where('late_alasan.idpeg', '=', (int) $userId)
                    ->where('late_alasan.jenisalasan_id', '=', 1)
                    ->where('late_alasan.is_deleted', '=', 0);
            })
            ->leftJoin('trans_alasan AS early_alasan', function ($join) use ($userId) {
                $join->on('early_alasan.log_datetime', '=', DB::raw('(SELECT MAX(trdatetime) FROM transit WHERE transit.staffid = early_alasan.idpeg)'))
                    ->where('early_alasan.idpeg', '=', (int) $userId)
                    ->where('early_alasan.jenisalasan_id', '=', 2)
                    ->where('early_alasan.is_deleted', '=', 0);
            })
            ->leftJoin('trans_alasan AS absent_alasan', function ($join) use ($userId) {
                $join->on('absent_alasan.log_datetime', '=', 'calendars.fulldate')
                    ->where('absent_alasan.idpeg', '=', (int) $userId)
                    ->where('absent_alasan.jenisalasan_id', '=', 3)
                    ->where('absent_alasan.is_deleted', '=', 0);
            })
            ->leftJoin('alasan AS alasan_late', 'late_alasan.alasan_id', '=', 'alasan_late.id')
            ->leftJoin('alasan AS alasan_early', 'early_alasan.alasan_id', '=', 'alasan_early.id')
            ->leftJoin('alasan AS alasan_absent', 'absent_alasan.alasan_id', '=', 'alasan_absent.id')
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
            alasan_late.diskripsi AS latereason,
            alasan_early.diskripsi AS earlyreason,
            alasan_absent.diskripsi AS absentreasont,
            late_alasan.status AS statuslate,
            early_alasan.status AS statusearly,
            absent_alasan.status AS statusabsent
        ', [
                $userId
            ])
            ->whereBetween('calendars.fulldate', [$startDay, $lastDay])
            ->where('calendars.isweekday', 1)
            ->where('calendars.isholiday', 0)
            ->whereNotNull('transit.trdatetime')
            ->groupBy(
                'calendars.fulldate',
                'calendars.year',
                'calendars.monthname',
                'calendars.dayname',
                'calendars.isweekday',
                'calendars.isholiday',
                'transit.staffid',
                'transit.ramadhan_yt',
                'alasan_late.diskripsi',
                'alasan_early.diskripsi',
                'alasan_absent.diskripsi',
                'late_alasan.status',
                'early_alasan.status',
                'absent_alasan.status'
            )
            ->orderBy('calendars.fulldate', 'ASC')
            ->get();

        return $results->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->fulldate));
            $record->box_color = $this->determineBoxColor($record->statusabsent ?? $record->statuslate ?? $record->statusearly);
            return $record;
        })->toArray();
    }

    public function fetchLateAttendanceRecords(int $userId, string $startDay, string $lastDay): array
    {
        // Fetch the staff ID for the given user
        $idStaff = User::where('is_deleted', '!=', 1)->where('id', $userId)->value('staffid');

        if (!$idStaff) {
            Log::error("Error: Staff ID not found for user ID: $userId");
            return [];
        }

        // Query to get late attendance records
        $records = DB::table('calendars AS c')
            ->leftJoin('transit AS t', function ($join) use ($idStaff) {
                $join->on(DB::raw('DATE(c.fulldate)'), '=', DB::raw('DATE(t.trdate)'))
                    ->where('t.staffid', $idStaff);
            })
            ->select(
                'c.fulldate',
                'c.year',
                'c.monthname',
                'c.dayname',
                'c.isweekday',
                'c.isholiday',
                't.staffid',
                DB::raw("$userId AS idpeg"),
                DB::raw('MIN(t.trdatetime) AS datetimein'),
                DB::raw("DATE_FORMAT(MIN(t.trdatetime), '%T') AS timein"),
                DB::raw("
                    CASE
                        WHEN c.isweekday = 1 AND c.isholiday = 0 AND TIME(MIN(t.trdatetime)) >= '09:01:00' THEN 1
                        ELSE 0
                    END AS latein
                "),
                DB::raw("
                    (
                        SELECT a.diskripsi
                        FROM trans_alasan AS ta
                        LEFT JOIN alasan AS a ON ta.alasan_id = a.id
                        WHERE ta.log_datetime = (
                            SELECT MIN(t2.trdatetime) 
                            FROM transit AS t2 
                            WHERE t2.staffid = t.staffid
                        )
                        AND ta.idpeg = $userId
                        AND ta.jenisalasan_id = 1
                        AND ta.is_deleted = 0
                        LIMIT 1
                    ) AS latereason
                "),
                DB::raw("
                    (
                        SELECT ta.status
                        FROM trans_alasan AS ta
                        WHERE ta.log_datetime = (
                            SELECT MIN(t2.trdatetime) 
                            FROM transit AS t2 
                            WHERE t2.staffid = t.staffid
                        )
                        AND ta.idpeg = $userId
                        AND ta.jenisalasan_id = 1
                        AND ta.is_deleted = 0
                        LIMIT 1
                    ) AS statuslate
                ")
            )
            ->whereBetween('c.fulldate', [$startDay, $lastDay])
            ->where('c.isweekday', 1)
            ->where('c.isholiday', 0)
            ->whereNotNull('t.trdatetime')
            ->groupBy(
                'c.fulldate',
                'c.year',
                'c.monthname',
                'c.dayname',
                'c.isweekday',
                'c.isholiday',
                't.staffid'
            )
            ->havingRaw('TIME(MIN(t.trdatetime)) >= "09:01:00"')
            ->orderBy('c.fulldate', 'ASC')
            ->get();

        // Map records for display and apply box color logic
        return $records->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->fulldate));
            $record->box_color = $this->determineBoxColor($record->statuslate);
            return (array) $record;
        })->toArray();
    }

    public function fetchAbsentRecords(int $userId): array
    {
        // Get the staff ID
        $idStaff = User::where('is_deleted', '!=', 1)
            ->where('id', $userId)
            ->value('staffid');

        if (!$idStaff) {
            Log::error("Error: Staff ID not found for user ID: $userId");
            return [];
        }


        $dayNow = Carbon::now()->format('d');
        $firstDayOfCurrentMonth = Carbon::now()->startOfMonth()->toDateString();
        $firstDayOfPreviousMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $startDay = $dayNow > 10 ? $firstDayOfCurrentMonth : $firstDayOfPreviousMonth;
        $endDay = Carbon::now()->toDateString(); // Always today

        // Main query for fetching absent records
        $records = DB::table('calendars')
            ->select(
                'calendars.fulldate',
                'calendars.isweekday',
                'calendars.isholiday',
                DB::raw("$userId AS idpeg"),
                DB::raw("(SELECT transit.staffid 
                      FROM transit 
                      WHERE transit.staffid = $idStaff
                      LIMIT 1
            ) AS staffid"),
                DB::raw("(SELECT alasan.diskripsi 
                          FROM trans_alasan 
                          LEFT JOIN alasan ON trans_alasan.alasan_id = alasan.id
                          WHERE DATE(trans_alasan.log_datetime) = DATE(calendars.fulldate)
                          AND trans_alasan.idpeg = $userId
                          AND trans_alasan.jenisalasan_id = 3
                          AND trans_alasan.is_deleted = 0
                          LIMIT 1
                ) AS absentreasont"),
                DB::raw("(SELECT trans_alasan.status 
                          FROM trans_alasan 
                          WHERE DATE(trans_alasan.log_datetime) = DATE(calendars.fulldate)
                          AND trans_alasan.idpeg = $userId
                          AND trans_alasan.jenisalasan_id = 3
                          AND trans_alasan.is_deleted = 0
                          LIMIT 1
                ) AS statusabsent"),
                DB::raw("(SELECT trans_alasan.catatan_peg
                          FROM trans_alasan 
                          WHERE DATE(trans_alasan.log_datetime) = DATE(calendars.fulldate)
                          AND trans_alasan.idpeg = $userId
                          AND trans_alasan.jenisalasan_id = 3
                          AND trans_alasan.is_deleted = 0
                          LIMIT 1
                ) AS catatan_peg"),
                DB::raw('DAYNAME(calendars.fulldate) AS dayname')
            )
            ->whereBetween('calendars.fulldate', [$startDay, $endDay]) // Apply date range
            ->where('calendars.isweekday', 1) // Only working days
            ->where('calendars.isholiday', 0) // Not a holiday
            ->whereNotExists(function ($query) use ($idStaff) {
                $query->select(DB::raw(1))
                    ->from('transit')
                    ->whereRaw('DATE(transit.trdate) = DATE(calendars.fulldate)')
                    ->where('transit.staffid', '=', $idStaff);
            }) // Ensure NO transit records exist
            ->orderBy('calendars.fulldate', 'ASC')
            ->get();

        // Format response
        return $records->map(function ($record) {
            return [
                'staffid' => $record->staffid ?? null,
                'day' => $record->dayname,
                'trdate' => $record->fulldate,
                'isweekday' => $record->isweekday,
                'isholiday' => $record->isholiday,
                'datetimeout' => null,
                'timeout' => null,
                'idpeg' => $record->idpeg,
                'earlyreason' => $record->absentreasont ?? null,
                'statusearly' => $record->statusabsent ?? null,
                'catatan_peg' => $record->catatan_peg ?? null, // Dynamically fetched field
                'date_display' => date('d/m/Y', strtotime($record->fulldate)),
                'box_color' => $this->determineBoxColor($record->statusabsent),
            ];
        })->toArray();
    }


    public function fetchEarlyLeaveRecords(int $userId, string $startDay, string $lastDay): array
    {
        // Get staff ID for the given user
        $idStaff = User::where('is_deleted', '!=', 1)->where('id', $userId)->value('staffid');

        if (!$idStaff) {
            Log::error("Error: Staff ID not found for user ID: $userId");
            return [];
        }

        // Query early leave records from lateinoutview
        $records = DB::table('lateinoutview AS l')
            ->select(
                'l.staffid',
                'l.day',
                'l.trdate',
                'l.isweekday',
                'l.isholiday',
                'l.datetimeout',
                'l.timeout',
                'l.earlyout',
                DB::raw("$userId AS idpeg"),
                DB::raw("
                    (
                        SELECT a.diskripsi
                        FROM trans_alasan AS ta
                        LEFT JOIN alasan AS a ON ta.alasan_id = a.id
                        WHERE ta.log_datetime = l.datetimeout
                        AND ta.idpeg = $userId
                        AND ta.jenisalasan_id = 2
                        AND ta.is_deleted = 0
                        LIMIT 1
                    ) AS earlyreason
                "),
                DB::raw("
                    (
                        SELECT ta.status
                        FROM trans_alasan AS ta
                        WHERE ta.log_datetime = l.datetimeout
                        AND ta.idpeg = $userId
                        AND ta.jenisalasan_id = 2
                        AND ta.is_deleted = 0
                        LIMIT 1
                    ) AS statusearly
                "),
                DB::raw("
                    (
                        SELECT ta.catatan_peg
                        FROM trans_alasan AS ta
                        WHERE ta.log_datetime = l.datetimeout
                        AND ta.idpeg = $userId
                        AND ta.jenisalasan_id = 2
                        AND ta.is_deleted = 0
                        LIMIT 1
                    ) AS catatan_peg
                ")
            )
            ->where('l.staffid', $idStaff)
            ->whereBetween('l.trdate', [$startDay, $lastDay])
            ->where('l.earlyout', 1)
            ->where('l.isweekday', 1)
            ->where('l.isholiday', 0)
            ->orderBy('l.trdate', 'ASC')
            ->get();

        // Format results
        return $records->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->trdate));
            $record->box_color = $this->determineBoxColor($record->statusearly);
            return (array) $record;
        })->toArray();
    }




    private function determineBoxColor($status = null)
    {
        if (is_null($status)) {
            return '#dc3545'; // Default red if status is missing
        }

        return match ($status) {
            4 => '#28a745', // Green
            2 => '#17a2b8', // Blue
            1, 3, 5 => '#ffc107', // Yellow
            default => '#dc3545', // Red
        };
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
            ? TransAlasan::where('is_deleted', '!=', 1) // Soft delete condition
                ->where('status', 1)
                ->whereYear('log_datetime', now()->year)
                ->whereMonth('log_datetime', now()->month)
                ->count()
            : TransAlasan::where('is_deleted', '!=', 1) // Soft delete condition
                ->where('status', 1)
                ->whereYear('log_datetime', now()->year)
                ->where(function ($query) use ($currentMonth, $lastMonth) {
                    $query->whereMonth('log_datetime', now()->month)
                        ->orWhere(function ($query) use ($lastMonth) {
                            $query->whereMonth('log_datetime', now()->subMonth()->month);
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
            ? TransAlasan::leftJoin('users', 'trans_alasan.idpeg', '=', 'users.id')
                ->where('trans_alasan.status', 1)
                ->where('users.reviewing_officer_id', $userId)
                ->where('trans_alasan.is_deleted', '!=', 1)
                ->whereYear('trans_alasan.log_datetime', now()->year)
                ->whereMonth('trans_alasan.log_datetime', now()->month)
                ->count()
            : TransAlasan::leftJoin('users', 'trans_alasan.idpeg', '=', 'users.id')
                ->where('trans_alasan.status', 1)
                ->where('users.reviewing_officer_id', $userId)
                ->where('trans_alasan.is_deleted', '!=', 1)
                ->whereYear('trans_alasan.log_datetime', now()->year)
                ->where(function ($query) use ($currentMonth, $lastMonth) {
                    $query->whereMonth('trans_alasan.log_datetime', now()->month)
                        ->orWhere(function ($query) use ($lastMonth) {
                            $query->whereMonth('trans_alasan.log_datetime', now()->subMonth()->month);
                        });
                })->count();
    }


    /**
     * Get approval count for admin roles.
     *
     * @param int $dayNow
     * @param string $currentMonth
     * @param string $lastMonth
     * @return int
     */
    public function getAdminApprovalCount(int $dayNow, string $currentMonth, string $lastMonth): int
    {
        return $dayNow > 10
            ? TransAlasan::where('is_deleted', '!=', 1) // Soft delete condition
                ->where('status', 2)
                ->whereYear('log_datetime', now()->year)
                ->whereMonth('log_datetime', now()->month)
                ->count()
            : TransAlasan::where('is_deleted', '!=', 1) // Soft delete condition
                ->where('status', 2)
                ->whereYear('log_datetime', now()->year)
                ->where(function ($query) use ($currentMonth, $lastMonth) {
                    $query->whereMonth('log_datetime', now()->month)
                        ->orWhere(function ($query) use ($lastMonth) {
                            $query->whereMonth('log_datetime', now()->subMonth()->month);
                        });
                })->count();
    }


    /**
     * Get approval count for approver roles.
     *
     * @param int $userId
     * @param int $dayNow
     * @param string $currentMonth
     * @param string $lastMonth
     * @return int
     */
    public function getApproverApprovalCount(int $userId, int $dayNow, string $currentMonth, string $lastMonth): int
    {
        return $dayNow > 10
            ? TransAlasan::leftJoin('users', 'trans_alasan.idpeg', '=', 'users.id')
                ->where('trans_alasan.status', 2)
                ->where('users.pengesah_id', $userId) // Assumes `pengesah_id` refers to approver ID
                ->where('trans_alasan.is_deleted', '!=', 1)
                ->whereYear('trans_alasan.log_datetime', now()->year)
                ->whereMonth('trans_alasan.log_datetime', now()->month)
                ->count()
            : TransAlasan::leftJoin('users', 'trans_alasan.idpeg', '=', 'users.id')
                ->where('trans_alasan.status', 2)
                ->where('users.pengesah_id', $userId)
                ->where('trans_alasan.is_deleted', '!=', 1)
                ->whereYear('trans_alasan.log_datetime', now()->year)
                ->where(function ($query) use ($currentMonth, $lastMonth) {
                    $query->whereMonth('trans_alasan.log_datetime', now()->month)
                        ->orWhere(function ($query) use ($lastMonth) {
                            $query->whereMonth('trans_alasan.log_datetime', now()->subMonth()->month);
                        });
                })->count();
    }


    public function getFilteredRecords(array $filters)
    {
        $userId = $filters['user_id'];
        $roleId = $filters['role_id'];
        $status = $filters['status'] ?? null;
        $months = is_array($filters['month']) ? $filters['month'] : [$filters['month']];
        $year = $filters['year'] ?? null;

        $query = DB::table('trans_alasan')
            ->select(
                'trans_alasan.id',
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
            ->where('trans_alasan.is_deleted', '!=', 1);

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

        $query->orderBy('trans_alasan.log_datetime', 'DESC');

        return $query->get()->map(function ($record) {
            return [
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

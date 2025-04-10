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

    /**
     * Get attendance records with details for a specific user and date range.
     *
     * @param int $userId
     * @param int $staffId
     * @param string $startDay
     * @param string $lastDay
     * @return array
     */

    //  public function getAttendanceRecordsWithDetails($userId, $staffId, $startDay, $lastDay)
    //  {
    //      Log::info('Fetching combined attendance records (late, early, absent)', [
    //          'userId' => $userId,
    //          'staffId' => $staffId,
    //          'startDay' => $startDay,
    //          'lastDay' => $lastDay,
    //      ]);
     
    //      $lateRecords = $this->fetchLateAttendanceRecords($userId, $startDay, $lastDay);
    //      $earlyRecords = $this->fetchEarlyLeaveRecords($userId, $startDay, $lastDay);
    //      $absentRecords = $this->fetchAbsentRecords($userId);
     
    //      $combined = array_merge($lateRecords, $earlyRecords, $absentRecords);
     
    //      usort($combined, function ($a, $b) {
    //          $dateA = $a['fulldate'] ?? $a['trdate'] ?? null;
    //          $dateB = $b['fulldate'] ?? $b['trdate'] ?? null;
     
    //          if (!$dateA || !$dateB) return 0;
     
    //          return strtotime($dateB) <=> strtotime($dateA); 
    //      });
     
    //      return $combined;
    //  }

    public function getAttendanceRecordsWithDetails($userId, $staffId, $startDay, $lastDay)
    {
        Log::info('Fetching combined attendance records (unified)', [
            'userId' => $userId,
            'staffId' => $staffId,
            'startDay' => $startDay,
            'lastDay' => $lastDay,
        ]);
    
        // Use the unified query method
        return $this->getAttendanceRecordsUnified($userId, $staffId, $startDay, $lastDay);
    }
    
     
    
    /**
     * Fetch late attendance records.
     *
     * @param int $userId
     * @param string $startDay
     * @param string $lastDay
     * @return array
     */
    
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
                DB::raw("CASE
                            WHEN c.isweekday = 1 
                            AND c.isholiday = 0 
                            AND TIME(MIN(t.trdatetime)) >= '09:01:00' 
                            THEN 1 
                            ELSE 0 
                         END AS latein"),
                DB::raw("(
                            SELECT a.diskripsi
                            FROM trans_alasan AS ta
                            LEFT JOIN alasan AS a ON ta.alasan_id = a.id
                            WHERE DATE(ta.log_datetime) = DATE(c.fulldate)
                            AND ta.idpeg = $userId
                            AND ta.jenisalasan_id = 1
                            AND ta.is_deleted = 0
                            LIMIT 1
                        ) AS latereason"),
                DB::raw("(
                            SELECT ta.status
                            FROM trans_alasan AS ta
                            WHERE DATE(ta.log_datetime) = DATE(c.fulldate)
                            AND ta.idpeg = $userId
                            AND ta.jenisalasan_id = 1
                            AND ta.is_deleted = 0
                            LIMIT 1
                        ) AS statuslate"),
                DB::raw("(
                            SELECT ta.catatan_peg
                            FROM trans_alasan AS ta
                            WHERE DATE(ta.log_datetime) = DATE(c.fulldate)
                            AND ta.idpeg = $userId
                            AND ta.jenisalasan_id = 1
                            AND ta.is_deleted = 0
                            LIMIT 1
                        ) AS catatan_peg")
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
            ->orderBy('c.fulldate', 'DESC')
            ->get();

        // Map records for display and apply box color logic
        return $records->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->fulldate));
            $record->box_color = $this->determineBoxColor($record->statuslate);
            $record->lewat_tanpa_sebab = true;
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

        // Get date values
        $dayNow = Carbon::now()->format('d');
        $firstDayOfCurrentMonth = Carbon::now()->startOfMonth()->toDateString();
        $firstDayOfPreviousMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $startDay = $dayNow > 10 ? $firstDayOfCurrentMonth : $firstDayOfPreviousMonth;
        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Check if there is attendance for today
        $hasTodayRecord = DB::table('transit')
            ->whereDate('trdate', $today)
            ->where('staffid', $idStaff)
            ->exists();

        // Ensure today is excluded from the query
        $endDay = $yesterday; // Always use yesterday to exclude today

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
            ->whereBetween('calendars.fulldate', [$startDay, $endDay]) // Use dynamic end date
            ->where('calendars.isweekday', 1) // Only working days
            ->where('calendars.isholiday', 0) // Not a holiday
            ->whereNotExists(function ($query) use ($idStaff) {
                $query->select(DB::raw(1))
                    ->from('transit')
                    ->whereRaw('DATE(transit.trdate) = DATE(calendars.fulldate)')
                    ->where('transit.staffid', '=', $idStaff);
            }) // Ensure NO transit records exist
            ->orderBy('calendars.fulldate', 'DESC')
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
                'tidak_hadir_tanpa_sebab' => true,
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
            ->orderBy('l.trdate', 'DESC')
            ->get();

        // Format results
        return $records->map(function ($record) {
            $record->date_display = date('d/m/Y', strtotime($record->trdate));
            $record->box_color = $this->determineBoxColor($record->statusearly);
            $record->balik_awal_tanpa_sebab = true;
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
            ->where('trans_alasan.status', Status::MENUNGGU_SEMAKAN) // Filter by status = 1
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


    public function getAttendanceRecordsUnified($userId, $staffId, $startDay, $lastDay)
    {
        Log::info('Fetching unified attendance records (late, early, absent)', [
            'userId' => $userId,
            'staffId' => $staffId,
            'startDay' => $startDay,
            'lastDay' => $lastDay,
        ]);
    
        $records = DB::table('calendars')
            ->leftJoin('transit', function ($join) use ($staffId) {
                $join->on(DB::raw('DATE(calendars.fulldate)'), '=', DB::raw('DATE(transit.trdate)'))
                    ->where('transit.staffid', '=', $staffId);
            })
            ->select(
                'calendars.fulldate',
                'calendars.year',
                'calendars.monthname',
                'calendars.dayname',
                'calendars.isweekday',
                'calendars.isholiday',
                DB::raw("$staffId AS staffid"),
                DB::raw("$userId AS idpeg"),
                DB::raw('MIN(transit.trdatetime) AS datetimein'),
                DB::raw("DATE_FORMAT(MIN(transit.trdatetime), '%T') AS timein"),
                DB::raw('MAX(transit.trdatetime) AS datetimeout'),
                DB::raw("DATE_FORMAT(MAX(transit.trdatetime), '%T') AS timeout"),
                DB::raw("CASE
                    WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND TIME(MIN(transit.trdatetime)) >= '09:01:00' THEN 1
                    ELSE 0 END AS latein"),
                DB::raw("CASE
                    WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND COALESCE(MAX(transit.ramadhan_yt), 0) = 0 AND TIME(MAX(transit.trdatetime)) <= '18:00:00'
                        AND (HOUR(TIMESTAMPADD(MINUTE, 540, MIN(transit.trdatetime))) * 60 + MINUTE(TIMESTAMPADD(MINUTE, 540, MIN(transit.trdatetime)))) > (HOUR(MAX(transit.trdatetime)) * 60 + MINUTE(MAX(transit.trdatetime)))
                        THEN 1
                    WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND COALESCE(MAX(transit.ramadhan_yt), 0) = 0
                        AND (TIME(MIN(transit.trdatetime)) = TIME(MAX(transit.trdatetime)) OR TIME(MAX(transit.trdatetime)) <= '16:30:00') THEN 1
                    WHEN calendars.isweekday = 1 AND calendars.isholiday = 0 AND COALESCE(MAX(transit.ramadhan_yt), 0) = 1 AND TIME(MAX(transit.trdatetime)) <= '18:00:00'
                        AND (HOUR(TIMESTAMPADD(MINUTE, 510, MIN(transit.trdatetime))) * 60 + MINUTE(TIMESTAMPADD(MINUTE, 510, MIN(transit.trdatetime)))) > (HOUR(MAX(transit.trdatetime)) * 60 + MINUTE(MAX(transit.trdatetime)))
                        THEN 1
                    WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND COALESCE(MAX(transit.ramadhan_yt), 0) = 1
                        AND (TIME(MIN(transit.trdatetime)) = TIME(MAX(transit.trdatetime)) OR TIME(MAX(transit.trdatetime)) <= '16:00:00') THEN 1
                    ELSE 0 END AS earlyout"),
                DB::raw("(SELECT a.diskripsi FROM trans_alasan ta LEFT JOIN alasan a ON ta.alasan_id = a.id
                          WHERE ta.log_datetime = MIN(transit.trdatetime) AND ta.idpeg = $userId AND ta.jenisalasan_id = 1 AND ta.is_deleted = 0 LIMIT 1) AS latereason"),
                DB::raw("(SELECT a.diskripsi FROM trans_alasan ta LEFT JOIN alasan a ON ta.alasan_id = a.id
                          WHERE ta.log_datetime = MAX(transit.trdatetime) AND ta.idpeg = $userId AND ta.jenisalasan_id = 2 AND ta.is_deleted = 0 LIMIT 1) AS earlyreason"),
                DB::raw("(SELECT a.diskripsi FROM trans_alasan ta LEFT JOIN alasan a ON ta.alasan_id = a.id
                          WHERE ta.log_datetime = calendars.fulldate AND ta.idpeg = $userId AND ta.jenisalasan_id = 3 AND ta.is_deleted = 0 LIMIT 1) AS absentreasont"),
                DB::raw("(SELECT ta.status FROM trans_alasan ta
                          WHERE ta.log_datetime = MIN(transit.trdatetime) AND ta.idpeg = $userId AND ta.jenisalasan_id = 1 AND ta.is_deleted = 0 LIMIT 1) AS statuslate"),
                DB::raw("(SELECT ta.status FROM trans_alasan ta
                          WHERE ta.log_datetime = MAX(transit.trdatetime) AND ta.idpeg = $userId AND ta.jenisalasan_id = 2 AND ta.is_deleted = 0 LIMIT 1) AS statusearly"),
                DB::raw("(SELECT ta.status FROM trans_alasan ta
                          WHERE ta.log_datetime = calendars.fulldate AND ta.idpeg = $userId AND ta.jenisalasan_id = 3 AND ta.is_deleted = 0 LIMIT 1) AS statusabsent")
            )
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
            ->orderBy('calendars.fulldate', 'DESC')
            ->get();
    
        return $records->map(function ($record) {
            $recordArray = (array) $record;
    
            $recordArray['date_display'] = date('d/m/Y', strtotime($record->fulldate));
            $recordArray['box_color'] = $this->determineBoxColorUnified($record);
    
            if ($record->latein) {
                $recordArray['lewat_tanpa_sebab'] = true;
            }
    
            if ($record->earlyout) {
                $recordArray['balik_awal_tanpa_sebab'] = true;
            }
    
            // If no punch at all
            if (!$record->datetimein && !$record->datetimeout) {
                $recordArray['tidak_hadir_tanpa_sebab'] = true;
            }
    
            return $recordArray;
        })->toArray();
    }
    


    private function determineBoxColorUnified($record)
    {
        $status = $record->statuslate ?? $record->statusearly ?? $record->statusabsent;

        return match ($status) {
            4 => '#28a745', // green
            2 => '#17a2b8', // blue
            1, 3, 5 => '#ffc107', // yellow
            default => '#dc3545', // red
        };
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

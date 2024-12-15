<?php

namespace App\Repositories;

use App\Models\OfficeLeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OfficeLeaveStatusRepository
{
    /**
     * Get department ID for a given user.
     *
     * @param int $userId
     * @return int|null
     */
    public function getDepartmentIdByUserId(int $userId): ?int
    {
        return DB::table('users')->where('id', $userId)->value('department_id');
    }

    /**
     * Fetch leave statuses based on filters and department.
     *
     * @param array $filters
     * @param int $departmentId
     * @return array
     */
    public function getLeaveStatuses(array $filters, int $departmentId): array
    {
        $monthStart = $filters['month_start'] ?? Carbon::now()->startOfMonth()->format('Y-m');
        $monthEnd = $filters['month_end'] ?? Carbon::now()->endOfMonth()->format('Y-m');

        $query = OfficeLeaveRequest::with(['creator', 'leaveType', 'approvalStatus'])
        ->whereHas('creator', function ($query) use ($departmentId, $filters) {
            $query->where('department_id', $departmentId);

            if (!empty($filters['pegawai_id'])) {
                $query->where('id', $filters['pegawai_id']);
            }
        })
            ->whereBetween('date', [
                Carbon::parse("$monthStart-01"),
                Carbon::parse("$monthEnd-01")->endOfMonth(),
            ])
            ->where('is_deleted', false);

        return $query->get()->map(function ($leave) {
            return [
                'nama_pegawai' => $leave->creator->name ?? 'N/A',
                'jawatan' => $leave->creator->position ?? 'N/A',
                'jenis' => $leave->leaveType->name ?? 'N/A',
                'tarikh_mula' => Carbon::parse($leave->date)->format('d/m/Y'),
                'hari' => $leave->day ?? 'N/A',
                'masa_mula' => $leave->start_time ? Carbon::parse($leave->start_time)->format('h:i A') : '',
                'masa_tamat' => $leave->end_time ? Carbon::parse($leave->end_time)->format('h:i A') : '',
                'bilangan_jam' => $leave->total_hours, // Using the new accessor
                'catatan' => $leave->reason ?? 'N/A',
                'status' => $leave->status,
                'diskripsi_status' => $leave->approvalStatus->status ?? 'N/A',
                'tarikh_kelulusan' => $leave->approval_date
                    ? Carbon::parse($leave->approval_date)->format('d/m/Y h:i:s A')
                    : 'N/A',
            ];
        })->toArray();
    }
}

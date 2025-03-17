<?php

namespace App\Services;

use App\Repositories\AttendanceApprovalRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TransAlasan;

class AttendanceApprovalService
{   
    protected $repository;
    public function __construct(AttendanceApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getApprovalList(int $userId, int $roleId, ?int $monthSearch = null, ?int $yearSearch = null)
    {
        return $this->repository->fetchApprovalList($userId, $roleId, $monthSearch, $yearSearch);
    }


    public function processBatchApprove(array $data, int $userId): array
    {
        $role = User::where('id', $userId)->value('role_id');
        $tralasanIds = $data['tralasan_id'] ?? [];
        $alasanStatus = $data['status_pengesah'];
        $notes = $data['catatan_pengesah'] ?? '';

        // Validate user role
        if (!in_array($role, [3, 6, 7, 9, 10, 12, 13, 16, 17])) {
            return ['status' => false, 'message' => 'User does not have permission to perform this action'];
        }

        // Retrieve TransAlasan records
        $transAlasan = TransAlasan::whereIn('id', $tralasanIds)->get();

        if ($transAlasan->isEmpty()) {
            return ['status' => false, 'message' => 'No records found'];
        }

        // Validate if all records have an approver
        $noApproverCount = User::whereNull('pengesah_id')
        ->whereIn('id', $transAlasan->pluck('idpeg'))
        ->count();

        if ($noApproverCount > 0) {
            return ['status' => false, 'message' => 'Some records do not have assigned approvers'];
        }

        // Update each TransAlasan record
        $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');
        foreach ($transAlasan as $record) {
            $record->pengesah_id = $userId;
            $record->status_pengesah = $alasanStatus;
            $record->catatan_pengesah = $notes;
            $record->tkh_pengesah_sah = $currentTimestamp;
            $record->status = $alasanStatus;
            $record->pengguna = $userId;
        
            if ($record->save()) {
                Log::info("Record {$record->id} updated successfully.");
            } else {
                Log::error("Failed to update record {$record->id}.");
            }
        }

        // Determine message based on status
        $message = match ($alasanStatus) {
            4 => 'Proses kemaskini rekod berjaya dan makluman pengesahan telah dihantar ke Pegawai Seliaan.',
            5 => 'Proses kemaskini rekod berjaya dan telah dihantar semula ke Pegawai Seliaan untuk tindakan selanjutnya.',
            6 => 'Proses kemaskini rekod berjaya dan telah dihantar semula ke Pegawai Seliaan untuk tindakan selanjutnya.',
            default => 'Kemaskini tidak diketahui.',
        };

        return ['status' => true, 'message' => $message];
    }
    
}

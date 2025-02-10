<?php

namespace App\Services;

use App\Repositories\AttendanceRecordRepository;
use App\Repositories\ReasonTransactionRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Status;
use App\Models\User;
use App\Models\ReasonTransaction;
use App\Models\TransAlasan;


class AttendanceReviewService
{
    protected $repository;
    protected $reasonTransactionRepository;

    public function __construct(AttendanceRecordRepository $repository, ReasonTransactionRepository $reasonTransactionRepository)
    {
        $this->repository = $repository;
        $this->reasonTransactionRepository = $reasonTransactionRepository;
    }

    public function getAttendanceRecordsForReview(array $filters)
    {
        return $this->repository->getFilteredRecords($filters);
    }

    public function batchUpdateReviewStatus(array $data)
    {
        return $this->repository->updateBatchReviewStatus($data['record_ids'], $data['review_status_id'], $data['review_notes']);
    }

    public function getMonthlyStatusSummary(int $month, int $year)
    {
        return $this->repository->getMonthlyStatusCounts($month, $year);
    }

    /**
     * Fetch review details for a given transaction ID.
     */
    public function getReviewDetails(int $id): ?array
    {
        $transaction = $this->reasonTransactionRepository->findById($id);

        if (!$transaction) {
            return null;
        }

        return [
            'fullname' => $transaction->employee->name ?? '', // Fetch employee's name
            'jawatan' => $transaction->employee->position ?? '', // Fetch employee's position
            'bahagian' => $transaction->employee->department->name ?? '', // Fetch employee's department
            'tarikh' => $transaction->log_timestamp->format('d/m/Y'), // Format log date
            'hari' => $transaction->log_timestamp->isoFormat('dddd'), // Day of the week
            'jenis_alasan' => $transaction->reasonType->description ?? '', // Fetch reason type description
            'sebab_alasan' => $transaction->reason->description ?? '', // Fetch reason description
            'catatan_pegawai' => $transaction->employee_notes, // Fetch employee notes
            'tarikh_penyelarasan' => $transaction->employee_reason_at
                ? $transaction->employee_reason_at->format('d/m/Y h:i:s A')
                : '', // Format adjustment date
            'status_options' => Status::whereNull('deleted_at')
            ->whereIn('id', [2, 3, 6])
                ->get(['id', 'description']), // Fetch status options
        ];
    }

    /**
     * Process attendance review logic.
     */
    public function processReview(array $data, int $userId): array
    {
        $transaction = $this->reasonTransactionRepository->findById($data['review_id']);

        if (!$transaction) {
            return ['status' => false, 'message' => 'Transaction not found.'];
        }

        $currentTimestamp = Carbon::now();

        // Update the transaction
        $updated = $this->reasonTransactionRepository->updateReview(
            $transaction->id,
            [
                'penyemak_id' => $userId,
                'status_penyemak' => $data['status'],
                'catatan_penyemak' => $data['notes'] ?? null,
                'tkh_penyemak_semak' => $currentTimestamp,
                'status' => $data['status'],
            ]
        );

        if (!$updated) {
            return ['status' => false, 'message' => 'Failed to update the transaction.'];
        }

        // Handle status-based logic
        switch ($data['status']) {
            case 2:
                $message = 'Transaction sent to approver.';
                break;
            case 3:
                $message = 'Transaction marked as not verified.';
                break;
            case 6:
                $this->handleNeedMoreInfo($transaction);
                $message = 'Transaction requires more information.';
                break;
            default:
                $message = 'Transaction updated successfully.';
        }

        return ['status' => true, 'message' => $message];
    }

    /**
     * Handle 'Need More Info' logic.
     */
    private function handleNeedMoreInfo($transaction): void
    {
        Log::info('Transaction ID ' . $transaction->id . ' marked as needing more information.');
    }

    public function processBatchReview(array $data, int $userId): array
    {
        $role = User::where('id', $userId)->value('role_id');
        $tralasanIds = $data['tralasan_ids'] ?? [];
        $alasanStatus = $data['status'];
        $notes = $data['catatanpengesah'] ?? '';

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

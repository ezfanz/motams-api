<?php

namespace App\Services;

use App\Repositories\AttendanceRecordRepository;
use App\Repositories\ReasonTransactionRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Status;
use App\Models\User;
use App\Models\ReasonTransaction;


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
        $status = $data['status'];
        $notes = $data['notes'] ?? '';

        if (!in_array($role, [3, 5, 7, 8, 10, 11, 13, 15, 17])) {
            return ['status' => false, 'message' => 'User does not have permission to perform this action'];
        }

        $transAlasan = ReasonTransaction::whereIn('id', $tralasanIds)->get();

        if ($transAlasan->isEmpty()) {
            return ['status' => false, 'message' => 'No records found'];
        }

        $noApproverCount = User::whereNull('approver_id')
            ->whereIn('id', $transAlasan->pluck('employee_id'))
            ->count();

        if ($noApproverCount > 0) {
            return ['status' => false, 'message' => 'Some records do not have assigned approvers'];
        }

        foreach ($transAlasan as $record) {
            $record->update([
                'reviewed_by' => $userId,
                'review_status' => $status,
                'review_notes' => $notes,
                'reviewed_at' => now(),
                'status' => $status,
            ]);

            // Additional actions based on status
            switch ($status) {
                case 2:
                    // Logic for success to approver
                    break;
                case 3:
                    // Logic for not verified
                    break;
                case 6:
                    // Logic for need more info
                    break;
            }
        }

        return ['status' => true, 'message' => 'Batch review processed successfully'];
    }

}

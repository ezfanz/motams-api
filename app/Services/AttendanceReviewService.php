<?php

namespace App\Services;

use App\Repositories\AttendanceRecordRepository;
use App\Repositories\ReasonTransactionRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        return $this->repository->filterByStatusAndDate($filters);
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
            'fullname' => $transaction->user->name,
            'jawatan' => $transaction->user->position,
            'bahagian' => $transaction->user->department->diskripsi ?? '',
            'tarikh' => $transaction->log_datetime->format('d/m/Y'),
            'hari' => $transaction->log_datetime->isoFormat('dddd'),
            'jenis_alasan' => $transaction->jenisAlasan->diskripsi_bm ?? '',
            'sebab_alasan' => $transaction->alasan->diskripsi ?? '',
            'catatan_pegawai' => $transaction->catatan_peg,
            'tarikh_penyelarasan' => $transaction->tkh_peg_alasan ? $transaction->tkh_peg_alasan->format('d/m/Y h:i:s A') : '',
            'status_options' => Status::whereNull('deleted_at')->whereIn('id', [2, 3, 6])->get(['id', 'diskripsi']),
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
                'reviewer_id' => $userId,
                'review_status' => $data['status'],
                'review_notes' => $data['notes'] ?? null,
                'reviewed_at' => $currentTimestamp,
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
}

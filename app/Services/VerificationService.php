<?php

namespace App\Services;

use App\Repositories\VerificationRepository;
use Carbon\Carbon;

class VerificationService
{
    protected $verificationRepository;

    public function __construct(VerificationRepository $verificationRepository)
    {
        $this->verificationRepository = $verificationRepository;
    }

    public function getRecordsForVerification(array $filters)
    {
        return $this->verificationRepository->filterByStatusAndDate($filters);
    }

    public function getStatusOptions()
    {
        return $this->verificationRepository->getStatusOptions();
    }

    public function batchUpdateVerificationStatus(array $data)
    {
        return $this->verificationRepository->updateBatchVerificationStatus($data['record_ids'], $data['verification_status_id'], $data['verification_notes']);
    }

    public function getMonthlyStatusSummary(int $month, int $year)
    {
        return $this->verificationRepository->getMonthlyVerificationSummary($month, $year);
    }

    /**
     * Process batch approval for attendance verifications.
     */
    public function processBatchApproval(array $data, int $userId): array
    {
        $transactions = $this->verificationRepository->findByIds($data['ids']);

        if ($transactions->isEmpty()) {
            return ['status' => false, 'message' => 'No valid records found for approval.'];
        }

        $currentDate = Carbon::now()->format('Y-m-d H:i:s');

        foreach ($transactions as $transaction) {
            $transaction->update([
                'approved_by' => $userId,
                'approval_status' => $data['status'],
                'approval_notes' => $data['notes'],
                'approved_at' => $currentDate,
                'status' => $data['status'], // Overall transaction status
            ]);
        }

        return ['status' => true, 'message' => 'Records successfully updated.'];
    }
}

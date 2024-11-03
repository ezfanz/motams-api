<?php

namespace App\Services;

use App\Repositories\VerificationRepository;

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
}

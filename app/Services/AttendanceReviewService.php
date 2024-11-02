<?php

namespace App\Services;

use App\Repositories\AttendanceRecordRepository;

class AttendanceReviewService
{
    protected $repository;

    public function __construct(AttendanceRecordRepository $repository)
    {
        $this->repository = $repository;
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
}

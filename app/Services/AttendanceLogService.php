<?php

namespace App\Services;

use App\Repositories\AttendanceLogRepository;

class AttendanceLogService
{
    protected $repository;

    public function __construct(AttendanceLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAttendanceLog($userId, $date)
    {
        return $this->repository->getByDate($userId, $date);
    }

    public function createOrUpdateAttendanceLog($userId, $data)
    {
        return $this->repository->createOrUpdate($userId, $data);
    }

    public function getAllLogsForUser($userId)
    {
        return $this->repository->allForUser($userId);
    }
}

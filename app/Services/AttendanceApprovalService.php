<?php

namespace App\Services;

use App\Repositories\AttendanceApprovalRepository;

class AttendanceApprovalService
{   
    protected $repository;
    public function __construct(AttendanceApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getApprovalList(int $userId, int $roleId, string $monthSearch, int $yearSearch)
    {
        return $this->repository->fetchApprovalList($userId, $roleId, $monthSearch, $yearSearch);
    }
    
}

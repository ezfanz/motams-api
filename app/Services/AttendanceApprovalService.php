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

    public function getApprovalList(int $userId, int $roleId, ?int $monthSearch = null, ?int $yearSearch = null)
    {
        return $this->repository->fetchApprovalList($userId, $roleId, $monthSearch, $yearSearch);
    }
    
}

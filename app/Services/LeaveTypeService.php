<?php

namespace App\Services;

use App\Repositories\LeaveTypeRepository;
use Illuminate\Database\Eloquent\Collection;

class LeaveTypeService
{
    protected $repository;

    public function __construct(LeaveTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Retrieve all leave types.
     *
     * @return Collection
     */
    public function getAllLeaveTypes(): Collection
    {
        return $this->repository->getAll();
    }
}

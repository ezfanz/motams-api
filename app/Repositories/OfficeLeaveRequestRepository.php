<?php

namespace App\Repositories;

use App\Models\OfficeLeaveRequest;
use Illuminate\Database\Eloquent\Collection;

class OfficeLeaveRequestRepository
{
    protected $model;

    public function __construct(OfficeLeaveRequest $model)
    {
        $this->model = $model;
    }

    /**
     * Get all office leave requests.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return OfficeLeaveRequest::with('leaveType', 'creator')->get();
    }

    /**
     * Find an office leave request by ID.
     *
     * @param int $id
     * @return OfficeLeaveRequest|null
     */
    public function findById(int $id): ?OfficeLeaveRequest
    {
        return OfficeLeaveRequest::with('leaveType', 'creator')->find($id);
    }

    /**
     * Create a new office leave request.
     *
     * @param array $data
     * @return OfficeLeaveRequest|null
     */
    public function create(array $data): ?OfficeLeaveRequest
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing office leave request.
     *
     * @param int $id
     * @param array $data
     * @return OfficeLeaveRequest|null
     */
    public function update(int $id, array $data): ?OfficeLeaveRequest
    {
        $leaveRequest = $this->findById($id);
        if ($leaveRequest) {
            $leaveRequest->update($data);
        }

        return $leaveRequest;
    }

    /**
     * Delete an office leave request by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $leaveRequest = $this->findById($id);
        return $leaveRequest ? $leaveRequest->delete() : false;
    }

     /**
     * Get leave requests by month and year.
     *
     * @param int $month
     * @param int $year
     * @return Collection
     */
    public function getByMonth(int $month, int $year): Collection
    {
        return OfficeLeaveRequest::with('leaveType', 'creator')
            ->whereYear('date_mula', $year) // ✅ Changed `date` to `date_mula`
            ->whereMonth('date_mula', $month) // ✅ Changed `date` to `date_mula`
            ->get();
    }
    

}

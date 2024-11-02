<?php

namespace App\Services;

use App\Repositories\OfficeLeaveRequestRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\OfficeLeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OfficeLeaveRequestService
{
    protected $repository;

    public function __construct(OfficeLeaveRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Retrieve all leave requests with duration.
     *
     * @return Collection
     */
    public function getAllLeaveRequests(): Collection
    {
        $leaveRequests = $this->repository->getAll();
        return $leaveRequests->map(function ($leaveRequest) {
            return $this->addDuration($leaveRequest);
        });
    }

    /**
     * Retrieve a single leave request by ID with duration.
     *
     * @param int $id
     * @return OfficeLeaveRequest|null
     */
    public function getLeaveRequestById(int $id): ?OfficeLeaveRequest
    {
        $leaveRequest = $this->repository->findById($id);
        return $leaveRequest ? $this->addDuration($leaveRequest) : null;
    }

    /**
     * Create a new leave request with duration.
     *
     * @param array $data
     * @return OfficeLeaveRequest
     */
    public function createLeaveRequest(array $data): OfficeLeaveRequest
    {
        $data['created_by'] = Auth::id();
        $leaveRequest = $this->repository->create($data);
        return $this->addDuration($leaveRequest);
    }

    /**
     * Update an existing leave request with duration.
     *
     * @param int $id
     * @param array $data
     * @return OfficeLeaveRequest|null
     */
    public function updateLeaveRequest(int $id, array $data): ?OfficeLeaveRequest
    {
        $leaveRequest = $this->repository->update($id, $data);
        return $leaveRequest ? $this->addDuration($leaveRequest) : null;
    }

    /**
     * Delete a leave request.
     *
     * @param int $id
     * @return bool
     */
    public function deleteLeaveRequest(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Calculate and add duration to the leave request response.
     *
     * @param OfficeLeaveRequest $leaveRequest
     * @return OfficeLeaveRequest
     */
    private function addDuration(OfficeLeaveRequest $leaveRequest): OfficeLeaveRequest
    {
        $startTime = Carbon::parse($leaveRequest->start_time);
        $endTime = Carbon::parse($leaveRequest->end_time);
        $duration = $startTime->diffInHours($endTime) . ' Jam';

        // Dynamically add 'duration' to the leave request model for the response
        $leaveRequest->duration = $duration;

        return $leaveRequest;
    }

    /**
     * Retrieve leave requests by month and year with total leave count.
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getLeaveRequestsByMonth(int $month, int $year): array
    {
        $leaveRequests = $this->repository->getByMonth($month, $year);
        $totalLeaveCount = $leaveRequests->count();

        return [
            'total_leave' => $totalLeaveCount,
            'leave_requests' => $leaveRequests,
        ];
    }
}

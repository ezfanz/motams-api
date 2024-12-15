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
     * Create a new leave request.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function createLeaveRequest(int $userId, array $data): array
    {
        $leaveData = [
            'created_by' => $userId,
            'leave_type_id' => $data['jenis'],
            'date' => $data['date'] ?? Carbon::now()->format('Y-m-d'), // Set default date if not provided
            'start_date' => $data['start_date'],
            'end_date' => $data['jenis'] == 1 ? $data['end_date'] : $data['start_date'],
            'day' => $data['day'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'total_days' => $data['total_days'] ?? null,
            'total_hours' => $data['total_hours'] ?? null,
            'reason' => $data['reason'],
            'status' => 15,
            'approval_status_id' => null,
            'approval_notes' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $leaveRequest = $this->repository->create($leaveData);

        if ($leaveRequest) {
            return [
                'status' => 'success',
                'message' => 'The leave request was successfully saved and sent to the approver for review.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to save the leave request.',
        ];
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

    public function countApprovalsForUser(int $userId): int
    {
        return OfficeLeaveRequest::where('status', '15')
            ->where('approval_status_id', $userId)
            ->count();
    }

}

<?php

namespace App\Services;

use App\Repositories\OfficeLeaveRequestRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\OfficeLeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        try {
            // Validate the required fields based on the leave type
            $leaveType = $data['jenis'];
            $leaveData = [
                'idpeg' => $userId,
                'leave_type_id' => $leaveType,
                'date_mula' => $data['tkh_mula'],
                'date_tamat' => $leaveType == 1 ? $data['tkh_hingga'] : $data['tkh_mula'],
                'day_timeoff' => $data['hari_timeoff'] ?? null,
                'start_time' => $data['masa_keluar'] ?? null,
                'end_time' => $data['masa_kembali'] ?? null,
                'reason' => $data['catatan'],
                'tkh_mohon' => Carbon::now()->format('Y-m-d H:i:s.u'),
                'status' => 15, // Pending status
                'id_pencipta' => $userId,
                'pengguna' => $userId,
            ];
            

            if ($leaveType == 1) { // Bekerja Luar Pejabat
                $leaveData['totalday'] = $data['bilhari'] ?? null;
                // Time Off
            } elseif ($leaveType == 2 && !empty($data['masa_keluar']) && !empty($data['masa_kembali'])) {
                $timeout = Carbon::createFromTimeString($data['masa_keluar']);
                $timeback = Carbon::createFromTimeString($data['masa_kembali']);
                $diffInMinutes = $timeout->diffInMinutes($timeback);
                $leaveData['totalhours'] = $diffInMinutes / 60;
            }
    
            Log::info("Creating Leave Request:", $leaveData);
                  
            $leaveRequest = OfficeLeaveRequest::create($leaveData);

            return [
                'status' => 'success',
                'message' => 'Proses simpan rekod berjaya dan telah dihantar ke Pegawai Pengesah untuk pengesahan.',
            ];
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if something fails
            Log::error('Leave request failed to save', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
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
            ->where('pelulus_id', $userId)
            ->count();
    }

}

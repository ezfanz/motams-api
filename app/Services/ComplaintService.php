<?php

namespace App\Services;

use App\Repositories\ComplaintRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComplaintService
{
    protected $complaintRepository;

    public function __construct(ComplaintRepository $complaintRepository)
    {
        $this->complaintRepository = $complaintRepository;
    }

    public function getAllComplaints()
    {
        return $this->complaintRepository->all();
    }

    public function getComplaintById($id)
    {
        try {
            return $this->complaintRepository->find($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Complaint not found.');
        }
    }

    public function createComplaint(array $data)
    {
        return $this->complaintRepository->create($data);
    }

    public function updateComplaint($id, array $data)
    {
        try {
            return $this->complaintRepository->update($id, $data);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Complaint not found for update.');
        }
    }

    public function deleteComplaint($id)
    {
        try {
            $this->complaintRepository->delete($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Complaint not found for deletion.');
        }
    }
}

<?php

namespace App\Repositories;

use App\Models\Complaint;

class ComplaintRepository
{
    /**
     * Get all complaints.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Complaint::with(['submittedBy', 'handledBy'])->get();
    }

    /**
     * Find a complaint by ID.
     *
     * @param int $id
     * @return \App\Models\Complaint
     */
    public function find($id)
    {
        return Complaint::with(['submittedBy', 'handledBy'])->findOrFail($id);
    }

    /**
     * Create a new complaint.
     *
     * @param array $data
     * @return \App\Models\Complaint
     */
    public function create(array $data)
    {
        return Complaint::create($data);
    }

    /**
     * Update a complaint by ID.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Complaint
     */
    public function update($id, array $data)
    {
        $complaint = $this->find($id);
        $complaint->update($data);

        return $complaint;
    }

    /**
     * Delete a complaint by ID.
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $complaint = $this->find($id);
        $complaint->delete();
    }
}

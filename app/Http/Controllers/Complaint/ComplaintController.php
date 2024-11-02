<?php

namespace App\Http\Controllers\Complaint;

use App\Http\Controllers\Controller;

use App\Services\ComplaintService;
use App\Http\Requests\Complaint\ComplaintRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComplaintController extends Controller
{
    protected $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    public function index(): JsonResponse
    {
        $complaints = $this->complaintService->getAllComplaints();
        return ResponseHelper::success($complaints, 'Complaints retrieved successfully');
    }

    public function show($id): JsonResponse
    {
        try {
            $complaint = $this->complaintService->getComplaintById($id);
            return ResponseHelper::success($complaint, 'Complaint retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error('Complaint not found', 404);
        }
    }

    public function store(ComplaintRequest $request): JsonResponse
    {
        $complaint = $this->complaintService->createComplaint($request->validated());
        return ResponseHelper::success($complaint, 'Complaint created successfully', 201);
    }

    public function update(ComplaintRequest $request, $id): JsonResponse
    {
        try {
            $complaint = $this->complaintService->updateComplaint($id, $request->validated());
            return ResponseHelper::success($complaint, 'Complaint updated successfully');
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error('Complaint not found for update', 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->complaintService->deleteComplaint($id);
            return ResponseHelper::success(null, 'Complaint deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error('Complaint not found for deletion', 404);
        }
    }
}

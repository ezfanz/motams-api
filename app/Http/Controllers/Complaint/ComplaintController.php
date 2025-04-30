<?php

namespace App\Http\Controllers\Complaint;

use App\Http\Controllers\Controller;

use App\Services\ComplaintService;
use App\Http\Requests\Complaint\ComplaintRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Mail;
use App\Mail\AduanEmail;
use Illuminate\Support\Facades\DB;


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

    /**
     * Handle the submission of Aduan.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendAduan(Request $request)
    {
        $validated = $request->validate([
            'tajuk_aduan' => 'required|string|max:255',
            'catatan_pegawai' => 'nullable|string|max:1000',
            'email' => 'required|email',
            // 'recipient_email' => 'required|email',
        ]);

        // Get authenticated user ID
        $userId = Auth::id();

        // Fetch user details (fullname, email, department)
        $user = DB::table('users')
            ->leftJoin('department', 'users.department_id', '=', 'department.id')
            ->select(
                'users.fullname',
                'users.email',
                'department.diskripsi AS nama_bahagian'
            )
            ->where('users.id', $userId)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User details not found.',
            ], 404);
        }

        // Email Data
        $aduanData = [
            'fullname' => $user->fullname,
            'email' => $user->email, // Sender Email
            'nama_bahagian' => $user->nama_bahagian ?? 'N/A',
            'tarikh_aduan' => now()->format('d/m/Y'),
            'tajuk_aduan' => $validated['tajuk_aduan'],
            'catatan_pegawai' => $validated['catatan_pegawai'] ?? 'N/A',
        ];

        // Send email with dynamic recipient
        Mail::to('msofri@mot.gov.my')->send(new AduanEmail($aduanData, 'msofri@mot.gov.my'));

        return response()->json([
            'status' => 'success',
            'message' => 'Aduan has been sent successfully.',
        ]);
    }



}

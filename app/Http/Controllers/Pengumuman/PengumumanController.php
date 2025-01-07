<?php

namespace App\Http\Controllers\Pengumuman;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengumuman;
use Carbon\Carbon;

class PengumumanController extends Controller
{
    /**
     * Fetch announcements based on the current date and department.
     */
    public function getPengumuman(Request $request)
    {
        $today = Carbon::today();
        $userId = auth()->id();
        $departmentId = auth()->user()->department_id; // Assuming department_id is in the users table

        $announcements = Pengumuman::where('is_deleted', 0)
            ->where(function ($query) use ($today) {
                $query->whereDate('tkhmula', '<=', $today)
                      ->whereDate('tkhtamat', '>=', $today);
            })
            ->where(function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId)
                      ->orWhereNull('department_id');
            })
            ->orderBy('tkhmula', 'asc')
            ->get(['id', 'butiran', 'tkhmula', 'tkhtamat', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data' => $announcements,
        ], 200);
    }
}

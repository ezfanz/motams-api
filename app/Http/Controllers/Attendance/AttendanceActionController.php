<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransAlasan;
use App\Http\Requests\Attendance\EarlyDepartureRequest;
use Carbon\Carbon;

class AttendanceActionController extends Controller
{
    public function handleEarlyDeparture(EarlyDepartureRequest $request)
    {
        $validated = $request->validated();

        $userId = $validated['idpeg'];
        $datetimeout = $validated['datetimeout'];
        $statusearly = $validated['statusearly'];

        // Get the box color
        $boxColor = $request->getBoxColor();

        // Fetch attendance status
        $statusDetails = $this->getAttendanceStatus($userId, $datetimeout);

        // Handle status logic
        if ($statusDetails) {
            switch ($statusDetails['status_code']) {
                case 4: // Green (Approved)
                    return response()->json([
                        'message' => 'Approved record. No changes allowed.',
                        'box_color' => $boxColor,
                    ], 200);

                case 2: // Blue (Pending Verification)
                    return response()->json([
                        'message' => 'Pending verification. Cannot edit.',
                        'box_color' => $boxColor,
                    ], 200);

                case 1: // Yellow (Pending Adjustment)
                case 3: // Yellow (Needs Correction)
                    $this->updateEarlyDepartureRecord($request);
                    return response()->json([
                        'message' => 'Record updated successfully.',
                        'box_color' => $boxColor,
                    ], 200);

                default:
                    return response()->json([
                        'message' => 'Unhandled status.',
                        'box_color' => $boxColor,
                    ], 400);
            }
        }

        // Handle new record creation if status is empty
        $this->createEarlyDepartureRecord($request);
        return response()->json([
            'message' => 'New record created successfully.',
            'box_color' => $boxColor,
        ], 201);
    }

    /**
     * Get attendance status based on user ID and datetimeout.
     */
    private function getAttendanceStatus($userId, $datetimeout)
    {
        return TransAlasan::select(
            'trans_alasan.status as status_code',
            'alasan.diskripsi as reason'
        )
            ->leftJoin('alasan', 'trans_alasan.alasan_id', '=', 'alasan.id')
            ->where('trans_alasan.idpeg', $userId)
            ->where('trans_alasan.log_datetime', $datetimeout)
            ->first();
    }

    /**
     * Create a new early departure record.
     */
    private function createEarlyDepartureRecord(Request $request)
    {
        TransAlasan::create([
            'idpeg' => $request->idpeg,
            'log_datetime' => $request->datetimeout,
            'alasan_id' => $request->statalasan,
            'jenisalasan_id' => 2,
            'catatan_peg' => $request->catatanpeg,
            'status' => 1, // Pending Adjustment
            'id_pencipta' => auth()->id(),
            'tkh_peg_alasan' => Carbon::now(),
        ]);
    }

    /**
     * Update an existing early departure record.
     */
    private function updateEarlyDepartureRecord(Request $request)
    {
        $trans = TransAlasan::find($request->transid);

        if ($trans) {
            $trans->update([
                'alasan_id' => $request->statalasan,
                'catatan_peg' => $request->catatanpeg,
                'status' => 1, // Pending Adjustment
                'id_pencipta' => auth()->id(),
                'tkh_peg_alasan' => Carbon::now(),
            ]);
        }
    }
}

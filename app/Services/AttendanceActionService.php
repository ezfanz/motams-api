<?php

namespace App\Services;

use App\Repositories\AttendanceActionRepository;
use Carbon\Carbon;
use App\Models\TransAlasan;

class AttendanceActionService
{
    private $attendanceActionRepository;

    public function __construct(AttendanceActionRepository $attendanceActionRepository)
    {
        $this->attendanceActionRepository = $attendanceActionRepository;
    }

    public function handleEarlyDeparture(array $data)
    {

        // Check if a transaction already exists
        $statusDetails = $this->attendanceActionRepository->getAttendanceStatus(
            $data['idpeg'],
            $data['datetimeout'],
            2 // Early Departure jenisalasan_id
        );

        $boxColor = $this->getBoxColor($statusDetails['status_code'] ?? null);

        // Check if record already exists
        $existingRecord = TransAlasan::where('idpeg', $data['idpeg'])
            ->where('log_datetime', $data['datetimeout']) // Ensure correct date
            ->where('jenisalasan_id', 2) // Early Departure
            ->where('is_deleted', 0)
            ->first();

        if ($existingRecord) {
            // **Instead of returning error, update the record**
            $this->attendanceActionRepository->updateRecord([
                'transid' => $existingRecord->id, // Pass existing transid
                'statalasan' => $data['statalasan'],
                'catatanpeg' => $data['catatanpeg'],
            ], 2);

            return $this->response('Record updated successfully.', $boxColor, 200);
        }

        // If no existing record, create a new one
        $this->attendanceActionRepository->createRecord($data, 2);
        return $this->response('New record created successfully.', $boxColor, 201);
    }



    public function handleLateArrival(array $data)
    {
        try {
            // Convert date format using Carbon::parse()
            $data['fulldate'] = Carbon::parse($data['fulldate'])->format('Y-m-d');
        } catch (\Exception $e) {
            return $this->response('Invalid date format. Expected format: d/m/Y or Y-m-d.', '#dc3545', 400);
        }
    
        // Check if a transaction already exists
        $existingRecord = TransAlasan::where('idpeg', $data['idpeg'])
            ->whereDate('log_datetime', $data['fulldate']) // Ensure correct date comparison
            ->where('jenisalasan_id', 1) // Late Arrival jenisalasan_id
            ->where('is_deleted', 0)
            ->first();
    
        $boxColor = $this->getBoxColor($existingRecord->status ?? null);
    
        if ($existingRecord) {
            // Update existing record
            $this->attendanceActionRepository->updateRecord([
                'transid' => $existingRecord->id, // Use existing transid
                'statalasan' => $data['statalasan'],
                'catatanpeg' => $data['catatanpeg'],
            ], 1);
    
            return $this->response('Late arrival record updated successfully.', $boxColor, 200);
        }
    
        // Create a new record if none exists
        $this->attendanceActionRepository->createRecord($data, 1);
        return $this->response('New late arrival record created successfully.', $boxColor, 201);
    }
    
    

    public function handleAbsent(array $data)
    {
        try {
            // Convert date format using Carbon::parse()
            $data['fulldate'] = Carbon::parse($data['fulldate'])->format('Y-m-d');
        } catch (\Exception $e) {
            return $this->response('Invalid date format. Expected format: d/m/Y or Y-m-d.', '#dc3545', 400);
        }

        // Check if a transaction already exists
        $existingRecord = TransAlasan::where('idpeg', $data['idpeg'])
            ->whereDate('log_datetime', $data['fulldate']) // Use `whereDate()` for date-only comparison
            ->where('jenisalasan_id', 3) // Tidak Hadir (Absent)
            ->where('is_deleted', 0)
            ->first();

        $boxColor = $this->getBoxColor($existingRecord->status ?? null);

        if ($existingRecord) {
            // Update existing record instead of returning an error
            $this->attendanceActionRepository->updateRecord([
                'transid' => $existingRecord->id, // Use existing transid
                'statalasan' => $data['statalasan'],
                'catatanpeg' => $data['catatanpeg'],
            ], 3);

            return $this->response('Absent record updated successfully.', $boxColor, 200);
        }

        // If no existing record, create a new one
        $this->attendanceActionRepository->createRecord($data, 3);
        return $this->response('New absent record created successfully.', $boxColor, 201);
    }



    private function getBoxColor(?int $statusCode)
    {
        return match ($statusCode) {
            4 => 'color:#28a745', // Green
            2 => 'color:#17a2b8', // Blue
            1, 3, 5 => 'color:#ffc107', // Yellow
            default => 'color:#dc3545', // Red
        };
    }

    private function response(string $message, string $boxColor, int $status)
    {
        return [
            'data' => [
                'message' => $message,
                'box_color' => $boxColor,
            ],
            'status' => $status,
        ];
    }
}

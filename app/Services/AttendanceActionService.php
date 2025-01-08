<?php

namespace App\Services;

use App\Repositories\AttendanceActionRepository;
use Carbon\Carbon;

class AttendanceActionService
{
    private $attendanceActionRepository;

    public function __construct(AttendanceActionRepository $attendanceActionRepository)
    {
        $this->attendanceActionRepository = $attendanceActionRepository;
    }

    public function handleEarlyDeparture(array $data)
    {
        $statusDetails = $this->attendanceActionRepository->getAttendanceStatus(
            $data['idpeg'],
            $data['datetimeout'],
            2 // Early Departure jenisalasan_id
        );

        $boxColor = $this->getBoxColor($statusDetails['status_code'] ?? null);

        if ($statusDetails) {
            switch ($statusDetails['status_code']) {
                case 4: // Green (Approved)
                    return $this->response('Approved record. No changes allowed.', $boxColor, 200);

                case 2: // Blue (Pending Verification)
                    return $this->response('Pending verification. Cannot edit.', $boxColor, 200);

                case 1: // Yellow (Pending Adjustment)
                case 3: // Yellow (Needs Correction)
                case 5: // Yellow
                    $this->attendanceActionRepository->updateRecord($data, 2);
                    return $this->response('Record updated successfully.', $boxColor, 200);

                default:
                    return $this->response('Unhandled status.', $boxColor, 400);
            }
        }

        // Create new record if no status found
        $this->attendanceActionRepository->createRecord($data, 2);
        return $this->response('New record created successfully.', $boxColor, 201);
    }

    public function handleLateArrival(array $data)
    {
        $statusDetails = $this->attendanceActionRepository->getAttendanceStatus(
            $data['idpeg'],
            $data['datetimein'],
            1 // Late Arrival jenisalasan_id
        );

        $boxColor = $this->getBoxColor($statusDetails['status_code'] ?? null);

        if ($statusDetails) {
            switch ($statusDetails['status_code']) {
                case 4: // Green (Approved)
                    return $this->response('Approved record. No changes allowed.', $boxColor, 200);

                case 2: // Blue (Pending Verification)
                    return $this->response('Pending verification. Cannot edit.', $boxColor, 200);

                case 1: // Yellow (Pending Adjustment)
                case 3: // Yellow (Needs Correction)
                case 5: // Yellow
                    $this->attendanceActionRepository->updateRecord($data, 1);
                    return $this->response('Record updated successfully.', $boxColor, 200);

                default:
                    return $this->response('Unhandled status.', $boxColor, 400);
            }
        }

        // Create new record if no status found
        $this->attendanceActionRepository->createRecord($data, 1);
        return $this->response('New record created successfully.', $boxColor, 201);
    }

    public function handleAbsent(array $data)
    {
        $statusDetails = $this->attendanceActionRepository->getAttendanceStatus(
            $data['idpeg'],
            $data['fulldate'],
            3 // jenisalasan_id for "Tidak Hadir"
        );

        $boxColor = $this->getBoxColor($statusDetails['status_code'] ?? null);

        if ($statusDetails) {
            switch ($statusDetails['status_code']) {
                case 4: // Green (Approved)
                    return $this->response('Approved record. No changes allowed.', $boxColor, 200);

                case 2: // Blue (Pending Verification)
                    return $this->response('Pending verification. Cannot edit.', $boxColor, 200);

                case 1: // Yellow (Pending Adjustment)
                case 3: // Yellow (Needs Correction)
                case 5: // Yellow
                    $this->attendanceActionRepository->updateRecord($data, 3);
                    return $this->response('Record updated successfully.', $boxColor, 200);

                default:
                    return $this->response('Unhandled status.', $boxColor, 400);
            }
        }

        // Create new record if no status found
        $this->attendanceActionRepository->createRecord($data, 3);
        return $this->response('New record created successfully.', $boxColor, 201);
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

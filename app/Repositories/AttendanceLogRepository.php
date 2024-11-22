<?php

namespace App\Repositories;

use App\Models\AttendanceLog;

class AttendanceLogRepository
{
    public function getByDate($userId, $date)
    {
        return AttendanceLog::where('user_id', $userId)->where('date', $date)->first();
    }

    public function createOrUpdate($userId, $data)
    {
        return AttendanceLog::updateOrCreate(
            ['user_id' => $userId, 'date' => $data['date']],
            $data
        );
    }

    public function delete($id)
    {
        $log = AttendanceLog::findOrFail($id);
        $log->delete();
        return $log;
    }

    public function allForUser($userId)
    {
        return AttendanceLog::where('user_id', $userId)->get();
    }
}

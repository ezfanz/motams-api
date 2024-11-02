<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OfficeLeaveRequest;
use App\Models\User;
use App\Models\LeaveType;
use Carbon\Carbon;

class SampleLeaveTyperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have leave types and a sample user to associate with the requests
        $leaveType = LeaveType::first();
        $user = User::first();

        // Create a few sample leave requests for the user
        $leaveRequests = [
            [
                'created_by' => $user->id,
                'leave_type_id' => $leaveType->id,
                'date' => Carbon::now()->format('Y-m-d'),
                'day' => Carbon::now()->format('l'),
                'start_time' => '09:00',
                'end_time' => '11:00',
                'reason' => 'Doctor appointment',
                'status' => 'Pending'
            ],
            [
                'created_by' => $user->id,
                'leave_type_id' => $leaveType->id,
                'date' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'day' => Carbon::now()->addDays(1)->format('l'),
                'start_time' => '13:00',
                'end_time' => '15:00',
                'reason' => 'Personal matter',
                'status' => 'Approved'
            ],
            [
                'created_by' => $user->id,
                'leave_type_id' => $leaveType->id,
                'date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'day' => Carbon::now()->addDays(2)->format('l'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'reason' => 'Family commitment',
                'status' => 'Rejected'
            ]
        ];

        foreach ($leaveRequests as $request) {
            OfficeLeaveRequest::create($request);
        }

    }
}

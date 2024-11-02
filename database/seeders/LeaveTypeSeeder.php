<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
   /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            ['name' => 'Time-off'],
            ['name' => 'Bekerja Luar Pejabat'],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate($type); // Ensure no duplicate entries
        }
    }
}

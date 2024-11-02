<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('attendance_statuses')->insert([
            [
                'id' => 1,
                'status_type' => 'Tidak Hadir',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'status_type' => 'Datang Lewat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'status_type' => 'Balik Awal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

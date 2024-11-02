<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call each seeder
        $this->call([
            AttendanceStatusSeeder::class,
            LeaveTypeSeeder::class,
            ReviewStatusSeeder::class,
            RoleSeeder::class,
        ]);
    }
}

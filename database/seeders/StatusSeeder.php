<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insert([
            ['description' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Rejected', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'More Information Needed', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColourSeeder extends Seeder
{
    public function run()
    {
        DB::table('colours')->insert([
            [
                'name' => 'Green',
                'description' => 'Status is approved or resolved',
                'hex_code' => '#28a745', // Hijau
            ],
            [
                'name' => 'Blue',
                'description' => 'Status is under review',
                'hex_code' => '#17a2b8', // Biru
            ],
            [
                'name' => 'Yellow',
                'description' => 'Status is pending or requires attention',
                'hex_code' => '#ffc107', // Kuning
            ],
            [
                'name' => 'Red',
                'description' => 'Status is rejected or critical',
                'hex_code' => '#dc3545', // Merah
            ],
        ]);
    }
}

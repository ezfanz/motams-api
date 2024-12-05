<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reason;

class ReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            ['id' => 1, 'description' => 'Menunggu Semakan', 'type' => null],
            ['id' => 2, 'description' => 'Alasan Diterima Penyemak', 'type' => null],
            ['id' => 3, 'description' => 'Alasan Tidak Diterima Penyemak', 'type' => null],
            ['id' => 4, 'description' => 'Alasan Diterima Pengesah', 'type' => null],
            ['id' => 5, 'description' => 'Alasan Tidak Diterima Pengesah', 'type' => null],
            ['id' => 6, 'description' => 'Memerlukan Maklumat Lanjut', 'type' => null],
            ['id' => 7, 'description' => 'Penukaran Warna Kad Asal Ke Hijau', 'type' => null],
            ['id' => 8, 'description' => 'Penukaran Warna Kad Hijau Ke Merah', 'type' => null],
            ['id' => 9, 'description' => 'Penukaran Warna Kad Kekal Hijau', 'type' => null],
            ['id' => 10, 'description' => 'Penukaran Warna Kad Kekal Merah', 'type' => null],
        ];

        foreach ($reasons as $reason) {
            Reason::create($reason);
        }
    }
}

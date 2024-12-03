<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestSeeder extends Seeder
{
    public function run()
    {
        // Add sample data to `transit` table
        DB::table('transit')->truncate();
        for ($i = 1; $i <= 10; $i++) {
            DB::table('transit')->insert([
                'staffid' => 1, // Replace with your user ID
                'trdate' => Carbon::now()->subDays($i)->toDateString(),
                'trdatetime' => Carbon::now()->subDays($i)->addHours(rand(8, 12))->toDateTimeString(),
                'card_number' => rand(1000, 9999),
                'terminal' => 'Terminal ' . rand(1, 5),
                'direction' => rand(0, 1),
                'strdirection' => rand(0, 1) ? 'IN' : 'OUT',
                'telegram_flag' => 1,
                'ramadhan_yt' => 0,
                'is_deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add sample data to `reasons` table
        DB::table('reasons')->truncate();
        DB::table('reasons')->insert([
            ['description' => 'Late due to traffic', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Late due to weather', 'created_at' => now(), 'updated_at' => now()],
            ['description' => 'Late due to personal reasons', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Add sample data to `reason_transactions` table
        DB::table('reason_transactions')->truncate();
        for ($i = 1; $i <= 5; $i++) {
            DB::table('reason_transactions')->insert(values: [
                'log_timestamp' => Carbon::now()->subDays($i)->toDateTimeString(),
                'employee_id' => 1, // Replace with your user ID
                'reason_id' => rand(1, 3),
                'reason_type_id' => 1, // Assuming type 1 is for late reasons
                'reasonable_type' => 'transit', // Polymorphic type
                'reasonable_id' => $i, // Assuming `transit` ID exists
                'status' => rand(1, 4), // Random status for testing
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
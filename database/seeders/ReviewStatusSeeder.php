<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewStatus;

class ReviewStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['status' => 'Pending-Review'],
            ['status' => 'Reviewed'],
            ['status' => 'Approved'],
            ['status' => 'Rejected']
        ];

        foreach ($statuses as $status) {
            ReviewStatus::create($status);
        }
    }
}

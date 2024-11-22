<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Calendar;
use Carbon\Carbon;

class CalendarSeeder extends Seeder
{
      /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = Carbon::now()->subYears(5); // Start 5 years in the past
        $endDate = Carbon::now()->addYears(5); // End 5 years in the future

        while ($startDate->lte($endDate)) {
            Calendar::updateOrCreate(
                ['fulldate' => $startDate->toDateString()], // Unique column for update or creation
                [
                    'dayname' => $startDate->format('l'),
                    'isweekday' => !in_array($startDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]),
                    'isholiday' => false, // Assume no holidays initially
                    'holidaydesc' => null,
                    'is_ramadhan' => false, // Default value
                ]
            );

            $startDate->addDay();
        }
    }
}

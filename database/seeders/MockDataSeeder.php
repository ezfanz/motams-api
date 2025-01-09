<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MockDataSeeder extends Seeder
{
    public function run()
    {
        // Departments
        DB::table('department')->insert([
            ['diskripsi' => 'HR', 'email' => 'hr@example.com', 'is_deleted' => 0, 'id_pencipta' => 1, 'pengguna' => 1],
            ['diskripsi' => 'IT', 'email' => 'it@example.com', 'is_deleted' => 0, 'id_pencipta' => 1, 'pengguna' => 1],
        ]);

        // Users
        DB::table('users')->insert([
            ['username' => 'user1', 'email' => 'user1@example.com', 'fullname' => 'John Doe', 'role_id' => 1, 'department_id' => 1, 'is_deleted' => 0],
        ]);

        // ActiveDepartments
        for ($i = 1; $i <= 10; $i++) {
            DB::table('active_departments')->insert([
                'idpeg' => 1,
                'staffid' => $i + 100,
                'datestarts' => Carbon::now()->subMonth($i),
                'dateends' => Carbon::now()->addMonth($i),
                'department_id' => 1, // Always department 1
                'is_deleted' => 0,
                'id_pencipta' => 1,
                'pengguna' => 1,
            ]);
        }

        // Employees
        for ($i = 1; $i <= 10; $i++) {
            DB::table('employee')->insert([
                'sbiid' => $i + 1000,
                'name' => "Employee $i",
                'surname' => "Surname $i",
                'nric' => "123456-$i",
                'card_number' => rand(1000, 9999),
                'commencementdatetime' => Carbon::now()->subYears($i),
                'expirydatetime' => Carbon::now()->addYears($i),
                'is_deleted' => 0,
                'id_pencipta' => 1,
                'pengguna' => 1,
            ]);
        }

        // OfficeLeaveRequests
        for ($i = 1; $i <= 10; $i++) {
            DB::table('office_leave_requests')->insert([
                'idpeg' => 1,
                'leave_type_id' => 1,
                'date_mula' => Carbon::now()->subDays($i),
                'date_tamat' => Carbon::now()->addDays($i),
                'reason' => "Reason $i",
                'tkh_mohon' => Carbon::now()->subDays($i),
                'pelulus_id' => 1,
                'catatan_pelulus' => "Notes for $i",
                'status' => 'Approved',
                'is_deleted' => 0,
                'id_pencipta' => 1,
                'pengguna' => 1,
            ]);
        }

        // Pengumuman
        for ($i = 1; $i <= 10; $i++) {
            DB::table('pengumuman')->insert([
                'butiran' => "Pengumuman $i",
                'tkhmula' => Carbon::now()->subDays($i),
                'tkhtamat' => Carbon::now()->addDays($i),
                'department_id' => 1,
                'is_deleted' => 0,
                'id_pencipta' => 1,
                'pengguna' => 1,
            ]);
        }

        // PenukaranWarna
        for ($i = 1; $i <= 10; $i++) {
            DB::table('penukaranwarna')->insert([
                'idpeg' => 1,
                'staffid' => rand(1000, 5000),
                'tarikhdari' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d H:i:s'),
                'tarikhhingga' => Carbon::now()->addDays(rand(1, 30))->format('Y-m-d H:i:s'),
                'bilkali' => rand(1, 10),
                'warna' => rand(1, 5),
                'status' => rand(0, 1),
                'srt_tnjk_sbb_yt' => rand(0, 1),
                'kelulusan_kj' => rand(0, 1),
                'catatan' => "Test note $i",
                'pgw_jana' => 1,
                'tkh_jana' => Carbon::now()->format('Y-m-d H:i:s'),
                'id_penyemak' => 1,
                'id_pengesah' => 1,
                'flag' => rand(0, 1),
                'is_deleted' => 0,
                'id_pencipta' => 1,
                'pengguna' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        // Transit
        for ($i = 1; $i <= 10; $i++) {
            DB::table('transit')->insert([
                'staffid' => 1, // Always points to user_id 1
                'trdate' => Carbon::now()->subDays($i),
                'trdatetime' => Carbon::now()->subHours(8),
                'card_number' => rand(1000, 9999),
                'terminal' => "Terminal $i",
                'direction' => 1,
                'strdirection' => 'Entry',
                'telegram_flag' => 1,
                'ramadhan_yt' => 0,
                'is_deleted' => 0,
            ]);
        }

        // TransAlasan
        for ($i = 1; $i <= 10; $i++) {
            DB::table('trans_alasan')->insert([
                'idpeg' => 1,
                'log_datetime' => Carbon::now()->subDays($i),
                'alasan_id' => 1,
                'jenisalasan_id' => 1,
                'catatan_peg' => "Reason for $i",
                'peg_alasan' => 1,
                'tkh_peg_alasan' => Carbon::now()->subDays($i),
                'penyemak_id' => 1,
                'status_penyemak' => 1,
                'catatan_penyemak' => "Notes $i",
                'tkh_penyemak_semak' => Carbon::now()->subHours(5),
                'status' => 1,
                'is_deleted' => 0,
            ]);
        }

        // WbfRamadhan
        for ($i = 1; $i <= 10; $i++) {
            DB::table('wbf_ramadhan')->insert([
                'idpeg' => 1,
                'staffid' => 1,
                'tarikh_dari' => Carbon::now()->subDays($i),
                'tarikh_hingga' => Carbon::now()->addDays($i),
                'pilih_yt' => 1,
                'id_pencipta' => 1,
                'pengguna' => 1,
            ]);
        }

        // KalendarRamadhan
        for ($i = 1; $i <= 10; $i++) {
            DB::table('kalendar_ramadhan')->insert([
                'tarikh_dari' => Carbon::now()->subDays($i * 2),
                'tarikh_hingga' => Carbon::now()->addDays($i * 2),
                'is_deleted' => 0,
                'id_pencipta' => 1,
                'pengguna' => 1,
            ]);
        }


        // Seed Transit (Late Arrival)
        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::now()->subDays($i);
            DB::table('transit')->insert([
                'staffid' => 1001,
                'trdate' => $date->format('Y-m-d'),
                'trdatetime' => $date->setTime(9, rand(1, 59))->format('Y-m-d H:i:s'),
                'terminal' => "Terminal $i",
                'direction' => 1,
                'telegram_flag' => 1,
                'ramadhan_yt' => 0,
                'is_deleted' => 0,
            ]);
        }

        // Seed Transit (Back Early)
        for ($i = 6; $i <= 10; $i++) {
            $date = Carbon::now()->subDays($i);
            DB::table('transit')->insert([
                'staffid' => 1001,
                'trdate' => $date->format('Y-m-d'),
                'trdatetime' => $date->setTime(rand(16, 17), rand(0, 59))->format('Y-m-d H:i:s'),
                'terminal' => "Terminal $i",
                'direction' => 2,
                'telegram_flag' => 1,
                'ramadhan_yt' => 0,
                'is_deleted' => 0,
            ]);
        }

        // Seed TransAlasan (Reasons for Late, Absent, Back Early)
        for ($i = 1; $i <= 5; $i++) {
            DB::table('trans_alasan')->insert([
                'idpeg' => 1,
                'log_datetime' => Carbon::now()->subDays($i)->format('Y-m-d H:i:s'),
                'alasan_id' => 1, // Late
                'jenisalasan_id' => 1,
                'catatan_peg' => "Late reason $i",
                'status' => 1,
                'is_deleted' => 0,
            ]);
        }

        for ($i = 6; $i <= 10; $i++) {
            DB::table('trans_alasan')->insert([
                'idpeg' => 1,
                'log_datetime' => Carbon::now()->subDays($i)->format('Y-m-d H:i:s'),
                'alasan_id' => 2, // Back Early
                'jenisalasan_id' => 2,
                'catatan_peg' => "Early reason $i",
                'status' => 1,
                'is_deleted' => 0,
            ]);
        }

        // Seed LateInOutView (Mock data for early leave)
        // for ($i = 6; $i <= 10; $i++) {
        //     DB::table('lateinoutview')->insert([
        //         'staffid' => 1001,
        //         'day' => Carbon::now()->subDays($i)->day,
        //         'trdate' => Carbon::now()->subDays($i)->format('Y-m-d'),
        //         'datetimeout' => Carbon::now()->subDays($i)->setTime(rand(16, 17), rand(0, 59))->format('Y-m-d H:i:s'),
        //         'earlyout' => 1,
        //         'isweekday' => 1,
        //         'isholiday' => 0,
        //     ]);
        // }

        for ($i = 1; $i <= 10; $i++) {
            DB::table('trans_alasan')->insert([
                'idpeg' => 1, // Always user ID 1
                'log_datetime' => Carbon::now()->subDays($i)->format('Y-m-d 00:00:00'), // Match the fulldate from calendars
                'alasan_id' => 3, // Absence reason
                'jenisalasan_id' => 3, // Reason type ID for absence
                'catatan_peg' => "Absent reason for day $i",
                'status' => 1, // Status for absence
                'is_deleted' => 0,
                'id_pencipta' => 1, // User ID for creator
                'pengguna' => 1, // User ID for pengguna
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            if ($i % 2 === 0) { // Add transit data only for even days
                DB::table('transit')->insert([
                    'staffid' => 1001, // Match the staff ID of user 1
                    'trdate' => Carbon::now()->subDays($i)->format('Y-m-d'),
                    'trdatetime' => Carbon::now()->subDays($i)->format('Y-m-d H:i:s'),
                    'card_number' => rand(1000, 9999),
                    'terminal' => "Terminal $i",
                    'direction' => 1,
                    'strdirection' => 'Entry',
                    'telegram_flag' => 1,
                    'ramadhan_yt' => 0,
                    'is_deleted' => 0,
                ]);
            }
        }

        // Seed TransAlasan (Reasons for Absence)
        $calendars = DB::table('calendars')
            ->whereBetween('fulldate', ['2024-12-01', '2024-12-31'])
            ->where('isweekday', 1)
            ->where('isholiday', 0)
            ->pluck('fulldate');

        foreach ($calendars as $date) {
            DB::table('trans_alasan')->insert([
                'idpeg' => 1, // User ID 1
                'log_datetime' => $date, // Align with calendars.fulldate
                'alasan_id' => 3, // Absence reason
                'jenisalasan_id' => 3, // Reason type ID for absence
                'catatan_peg' => "Absent reason for date $date",
                'status' => 1, // Status for absence
                'is_deleted' => 0,
                'id_pencipta' => 1, // User ID for creator
                'pengguna' => 1, // User ID for pengguna
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed Calendars Table
        $startDay = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDay = Carbon::now()->endOfMonth()->format('Y-m-d');

        $dates = Carbon::parse($startDay);
        while ($dates->lte(Carbon::parse($endDay))) {
            DB::table('calendars')->insert([
                'fulldate' => $dates->format('Y-m-d'),
                'year' => $dates->year,
                'monthname' => $dates->format('F'),
                'dayname' => $dates->format('l'),
                'isweekday' => $dates->isWeekday() ? 1 : 0,
                'isholiday' => 0, // Assuming no holidays for this seeder
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $dates->addDay();
        }

        // Seed Transit Table for Late Arrivals
        for ($i = 1; $i <= 5; $i++) { // 5 late attendance records
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $minute = str_pad(rand(2, 30), 2, '0', STR_PAD_LEFT); // Ensure two-digit minute
            DB::table('transit')->insert([
                'staffid' => 1001, // Assuming staff ID for user 1
                'trdate' => $date,
                'trdatetime' => Carbon::createFromFormat('Y-m-d H:i:s', "$date 09:$minute:00"),
                'terminal' => "Terminal $i",
                'direction' => 1,
                'strdirection' => 'Entry',
                'telegram_flag' => 1,
                'ramadhan_yt' => 0,
                'is_deleted' => 0,
            ]);
        }

        // Seed TransAlasan Table for Late Reasons
        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $minute = str_pad(rand(2, 30), 2, '0', STR_PAD_LEFT); // Ensure two-digit minute
            DB::table('trans_alasan')->insert([
                'idpeg' => 1, // User ID 1
                'log_datetime' => Carbon::createFromFormat('Y-m-d H:i:s', "$date 09:$minute:00"),
                'alasan_id' => 1, // Late reason ID
                'jenisalasan_id' => 1, // Type for lateness
                'catatan_peg' => "Late reason for day $i",
                'status' => 1, // Approved
                'is_deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

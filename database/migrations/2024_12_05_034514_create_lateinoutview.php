<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
        CREATE ALGORITHM=UNDEFINED 
        DEFINER=`root`@`localhost` 
        SQL SECURITY DEFINER 
        VIEW `lateinoutview` AS 
        SELECT 
            transit.staffid AS staffid,
            CASE 
                WHEN DAYNAME(transit.trdate) = 'Monday' THEN 'Isnin'
                WHEN DAYNAME(transit.trdate) = 'Tuesday' THEN 'Selasa'
                WHEN DAYNAME(transit.trdate) = 'Wednesday' THEN 'Rabu'
                WHEN DAYNAME(transit.trdate) = 'Thursday' THEN 'Khamis'
                WHEN DAYNAME(transit.trdate) = 'Friday' THEN 'Jumaat'
                WHEN DAYNAME(transit.trdate) = 'Saturday' THEN 'Sabtu'
                WHEN DAYNAME(transit.trdate) = 'Sunday' THEN 'Ahad'
            END AS day,
            transit.trdate AS trdate,
            calendars.isholiday AS isholiday,
            calendars.isweekday AS isweekday,
            transit.ramadhan_yt AS ramadhan_yt,
            MIN(transit.trdatetime) AS datetimein,
            DATE_FORMAT(MIN(transit.trdatetime), '%T') AS timein,
            CASE 
                WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 
                    AND DATE_FORMAT(MIN(transit.trdatetime), '%T') >= '09:01' THEN 1
                ELSE 0
            END AS latein,
            MAX(transit.trdatetime) AS datetimeout,
            DATE_FORMAT(MAX(transit.trdatetime), '%T') AS timeout,
            CASE 
                WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND transit.ramadhan_yt = 0 
                    AND CAST(MAX(transit.trdatetime) AS TIME) <= '18:00:00'
                    AND HOUR(MIN(transit.trdatetime) + INTERVAL 540 MINUTE) * 60 
                        + MINUTE(MIN(transit.trdatetime) + INTERVAL 540 MINUTE) > 
                      HOUR(MAX(transit.trdatetime)) * 60 + MINUTE(MAX(transit.trdatetime)) THEN 1
                WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND transit.ramadhan_yt = 0 
                    AND (CAST(MIN(transit.trdatetime) AS TIME) = CAST(MAX(transit.trdatetime) AS TIME) 
                        OR CAST(MAX(transit.trdatetime) AS TIME) <= '16:30:00') THEN 1
                WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND transit.ramadhan_yt = 1 
                    AND CAST(MAX(transit.trdatetime) AS TIME) <= '18:00:00'
                    AND HOUR(MIN(transit.trdatetime) + INTERVAL 510 MINUTE) * 60 
                        + MINUTE(MIN(transit.trdatetime) + INTERVAL 510 MINUTE) > 
                      HOUR(MAX(transit.trdatetime)) * 60 + MINUTE(MAX(transit.trdatetime)) THEN 1
                WHEN calendars.isholiday = 0 AND calendars.isweekday = 1 AND transit.ramadhan_yt = 1 
                    AND (CAST(MIN(transit.trdatetime) AS TIME) = CAST(MAX(transit.trdatetime) AS TIME) 
                        OR CAST(MAX(transit.trdatetime) AS TIME) <= '16:00:00') THEN 1
                ELSE 0
            END AS earlyout
        FROM 
            transit
        LEFT JOIN 
            calendars ON (transit.trdate = calendars.fulldate)
        WHERE 
            transit.trdate >= '2023-08-01'
        GROUP BY 
            transit.staffid, transit.trdate, calendars.dayname, calendars.isholiday, calendars.isweekday, transit.ramadhan_yt;
    ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `lateinoutview`;");
    }
};

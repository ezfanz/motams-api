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
        Schema::table('office_leave_requests', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('leave_type_id')->comment('Start date of the leave request');
            $table->date('end_date')->nullable()->after('start_date')->comment('End date of the leave request');
            $table->double('total_days')->nullable()->after('end_date')->comment('Total number of days for the leave');
            $table->double('total_hours')->nullable()->after('total_days')->comment('Total number of hours for time-off requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_leave_requests', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'total_days', 'total_hours']);
        });
    }
};

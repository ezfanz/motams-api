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
            $table->timestamp('approval_date')->nullable()->after('approval_status_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_leave_requests', function (Blueprint $table) {
            $table->dropColumn('approval_date');
        });
    }
};

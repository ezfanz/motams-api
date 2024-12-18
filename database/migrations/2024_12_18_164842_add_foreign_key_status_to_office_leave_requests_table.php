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
            // Ensure the `statuses` table exists and `status` column in office_leave_requests is integer
            $table->unsignedBigInteger('status')->nullable()->change();
            $table->foreign('status')->references('id')->on('statuses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_leave_requests', function (Blueprint $table) {
            $table->dropForeign(['status']);
        });
    }
};

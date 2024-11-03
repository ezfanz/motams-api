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
            $table->foreignId('approval_status_id')->nullable()->constrained('review_statuses')->onDelete('set null');
            $table->text('approval_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approval_status_id']);
            $table->dropColumn('approval_status_id');
            $table->dropColumn('approval_notes');
        });
    }
};

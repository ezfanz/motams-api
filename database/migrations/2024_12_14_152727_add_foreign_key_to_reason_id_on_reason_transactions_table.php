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
        Schema::table('reason_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('reason_id')->nullable()->change(); // Ensure `reason_id` is unsigned
            $table->foreign('reason_id')->references('id')->on('reasons')->onDelete('set null'); // Add foreign key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reason_transactions', function (Blueprint $table) {
            $table->dropForeign(['reason_id']); // Drop the foreign key
        });
    }
};

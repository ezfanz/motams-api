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
        Schema::create('office_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Related to user_id in users table
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->date('date'); // Date of leave request
            $table->string('day'); // Day of the week
            $table->time('start_time'); // Start time of leave
            $table->time('end_time'); // End time of leave
            $table->text('reason')->nullable(); // Reason for leave
            $table->string('status')->default('Pending'); // Status of the request
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_leave_requests');
    }
};

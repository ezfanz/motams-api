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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->date('date'); // Date of complaint
            $table->string('day', 50); // Day of the week in Malay
            $table->string('complaint_type', 100); // Type of complaint
            $table->string('complaint_title', 255); // Title of complaint
            $table->text('officer_notes')->nullable(); // Notes from the officer handling the complaint
            $table->enum('status', ['Pending', 'Resolved', 'Closed'])->default('Pending'); // Status of complaint
            $table->timestamps(); // Created at and Updated at timestamps
            $table->softDeletes(); // Soft delete column

            // Foreign keys
            $table->unsignedBigInteger('submitted_by')->nullable(); // Reference to user who submitted the complaint
            $table->unsignedBigInteger('handled_by')->nullable(); // Reference to officer handling the complaint

            // Foreign key constraints
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('handled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['handled_by']);
        });

        Schema::dropIfExists('complaints');
    }
};

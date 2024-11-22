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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->date('fulldate')->unique(); // Full date
            $table->string('dayname'); // E.g., Monday, Tuesday
            $table->boolean('isweekday')->default(true); // Is it a weekday (e.g., Monday-Friday)
            $table->boolean('isholiday')->default(false); // Is it a public holiday
            $table->string('holidaydesc')->nullable(); // Description of the holiday
            $table->boolean('is_ramadhan')->default(false); // Special period like Ramadhan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};

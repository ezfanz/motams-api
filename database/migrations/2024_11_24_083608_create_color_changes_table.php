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
        Schema::create('colour_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Changed idpeg to user_id
            $table->integer('staff_id')->nullable(); // Staff ID retained for external mapping
            $table->datetime('start_date')->nullable(); // Changed tarikhdari to start_date
            $table->datetime('end_date')->nullable(); // Changed tarikhhingga to end_date
            $table->integer('count')->nullable(); // Changed bilkali to count
            $table->tinyInteger('colour')->nullable(); // Changed warna to color
            $table->foreignId('colour_id')->nullable()->constrained('colours')->onDelete('set null'); // Changed warna to color_id
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('warning_letter')->nullable(); // Changed srt_tnjk_sbb_yt to warning_letter
            $table->tinyInteger('approval_status')->nullable(); // Changed kelulusan_kj to approval_status
            $table->text('notes')->nullable(); // Changed catatan to notes
            $table->integer('generated_by')->nullable(); // Changed pgw_jana to generated_by
            $table->datetime('generated_at')->nullable(); // Changed tkh_jana to generated_at
            $table->foreignId('reviewing_officer_id')->nullable()->constrained('users')->onDelete('set null'); // Changed id_pencipta to reviewing_officer_id
            $table->foreignId('approving_officer_id')->nullable()->constrained('users')->onDelete('set null'); // Changed pengguna to approving_officer_id
            $table->tinyInteger('flag')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('start_date', 'idx_start_date'); // Changed idx_tarikhdari to idx_start_date
        });

        // Add staff_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('staff_id')->nullable()->after('id'); // Add staff_id column to users table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('color_changes');

        // Remove staff_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('staff_id');
        });
    }
};

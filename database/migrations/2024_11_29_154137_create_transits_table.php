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
        Schema::create('transit', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('staffid')->nullable();
            $table->date('trdate')->nullable();
            $table->dateTime('trdatetime')->nullable();
            $table->integer('card_number')->nullable();
            $table->string('terminal', 50)->nullable();
            $table->tinyInteger('direction')->nullable();
            $table->string('strdirection', 30)->nullable();
            $table->tinyInteger('telegram_flag')->default(1);
            $table->tinyInteger('ramadhan_yt')->default(0);
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamps(); // Adds `created_at` and `updated_at`
            $table->softDeletes();


            // Keys
            $table->unique(['staffid', 'trdatetime'], 'staffid_trdatetime_unique');
            $table->index(['staffid', 'trdate', 'trdatetime'], 'idx_staffid_trdate_trdatetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transits');
    }
};

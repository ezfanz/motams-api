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
        Schema::create('reason_types', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable(); // Type description
            $table->timestamps();
        });

         // Add foreign key to reason_transactions table
         Schema::table('reason_transactions', function (Blueprint $table) {
            $table->foreign('reason_type_id')
                ->references('id')
                ->on('reason_types')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reason_transactions', function (Blueprint $table) {
            $table->dropForeign(['reason_type_id']);
        });

        Schema::dropIfExists('reason_types');
    }
};

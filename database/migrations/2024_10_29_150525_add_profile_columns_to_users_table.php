<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Adding the username column
            $table->string('username')->unique()->nullable()->after('name'); // Unique username

            // Adding position and department columns
            $table->string('position')->nullable()->after('username'); // Job title/position
            $table->string('department')->nullable()->after('position'); // Department

            // Adding foreign keys for reviewing and approving officers
            $table->unsignedBigInteger('reviewing_officer_id')->nullable()->after('department'); // Reviewing Officer
            $table->unsignedBigInteger('approving_officer_id')->nullable()->after('reviewing_officer_id'); // Approving Officer

            // Foreign key constraints (assuming these are other users in the same table)
            $table->foreign('reviewing_officer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approving_officer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys and columns in reverse order
            $table->dropForeign(['reviewing_officer_id']);
            $table->dropForeign(['approving_officer_id']);
            $table->dropColumn(['username', 'position', 'department', 'reviewing_officer_id', 'approving_officer_id']);
        });
    }
};

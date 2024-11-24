<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('office_leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('reviewer_id')->nullable()->after('created_by');

            // Add foreign key constraint
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('office_leave_requests', function (Blueprint $table) {
            $table->dropForeign(['reviewer_id']);
            $table->dropColumn('reviewer_id');
        });
    }
};

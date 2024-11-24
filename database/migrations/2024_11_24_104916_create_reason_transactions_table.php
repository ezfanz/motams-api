<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReasonTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('reason_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamp('log_timestamp')->nullable();
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->unsignedBigInteger('reason_type_id')->nullable();
            $table->text('employee_notes')->nullable();
            $table->unsignedBigInteger('employee_reason_by')->nullable();
            $table->timestamp('employee_reason_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->unsignedInteger('review_status')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedInteger('approval_status')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('related_user_id')->nullable();
            $table->unsignedInteger('status')->nullable();
            $table->softDeletes();

            // Polymorphic fields
            $table->morphs('reasonable'); // Adds 'reasonable_type' and 'reasonable_id'

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Unique index with a custom name
            $table->unique(['employee_id', 'log_timestamp', 'reason_type_id'], 'reason_txn_unique_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reason_transactions');
    }
}

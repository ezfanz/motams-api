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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User who receives the notification
            $table->string('type'); // Type of notification, e.g., 'attendance', 'task', 'event'
            $table->morphs('notifiable'); // Polymorphic relationship
            $table->text('message'); // Notification message
            $table->enum('status', ['Unread', 'Read'])->default('Unread'); // Status of the notification
            $table->json('metadata')->nullable(); // Additional metadata for the notification
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

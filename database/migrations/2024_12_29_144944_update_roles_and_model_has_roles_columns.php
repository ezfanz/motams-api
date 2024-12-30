<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update roles.id to match unsignedBigInteger
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->change();
        });

        // Update model_has_roles.role_id to match unsignedBigInteger
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->change();
        });
    }

    public function down(): void
    {
        // Reverse the changes if needed
        Schema::table('roles', function (Blueprint $table) {
            $table->bigInteger('id')->change();
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->bigInteger('role_id')->change();
        });
    }
};

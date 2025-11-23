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
        Schema::table('users', function (Blueprint $table) {
            // Employee ID နောက်မှာ Fingerprint ID ထည့်မယ် (Unique ဖြစ်ရမယ်)
            $table->string('finger_print_id')->nullable()->unique()->after('employee_id');
            
            // Phone နောက်မှာ Location ထည့်မယ်
            $table->string('location')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['finger_print_id', 'location']);
        });
    }
};

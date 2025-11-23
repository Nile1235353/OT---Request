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
        Schema::create('ot_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique(); // ဥပမာ OT-2025-001
            $table->foreignId('supervisor_id')->constrained('users'); // users table ကိုညွှန်း
            $table->date('ot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_hours', 5, 2);
            $table->text('reason');
            $table->string('status')->default('pending'); // pending, approved, rejected, acknowledged, completed
            $table->string('requirement_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ot_requests');
    }
};

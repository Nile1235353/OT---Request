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
        Schema::table('ot_requests', function (Blueprint $table) {
            // request_id ပြီးနောက် job_code ဆိုတဲ့ column တိုးပါမယ်
            $table->string('job_code')->nullable()->after('request_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ot_requests', function (Blueprint $table) {
            $table->dropColumn('job_code');
        });
    }
};

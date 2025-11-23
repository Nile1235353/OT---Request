<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ot_requests', function (Blueprint $table) {
            // status column ပြီးနောက် remark column ထည့်မယ် (Text အမျိုးအစား)
            $table->text('reject_remark')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ot_requests', function (Blueprint $table) {
            $table->dropColumn('reject_remark');
        });
    }
};

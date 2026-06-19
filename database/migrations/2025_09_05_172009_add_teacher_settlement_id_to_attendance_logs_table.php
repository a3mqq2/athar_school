<?php
// database/migrations/2025_09_05_000001_add_settlement_id_to_attendance_logs.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->foreignId('teacher_settlement_id')->nullable()->after('notes')->constrained('teacher_settlements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_settlement_id');
        });
    }
};

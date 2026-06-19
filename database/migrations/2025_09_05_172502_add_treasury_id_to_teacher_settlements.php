<?php
// database/migrations/2025_09_05_000002_add_treasury_id_to_teacher_settlements.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_settlements', function (Blueprint $table) {
            $table->foreignId('treasury_id')->nullable()->after('teacher_id')->constrained('treasuries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_settlements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('treasury_id');
        });
    }
};

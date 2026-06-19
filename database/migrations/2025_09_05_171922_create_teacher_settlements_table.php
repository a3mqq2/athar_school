<?php
// database/migrations/2025_09_05_000000_create_teacher_settlements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('total_lessons')->default(0);
            $table->decimal('session_price', 12, 2)->default(0);
            $table->decimal('calculated_amount', 14, 2)->default(0);
            $table->decimal('settled_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_settlements');
    }
};
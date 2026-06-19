<?php
// database/migrations/2025_09_02_180000_create_student_enrollments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            // الطالب لا يمكن أن يسجل أكثر من مرة في نفس السنة
            $table->unique(['student_id','academic_year_id'], 'uniq_student_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};

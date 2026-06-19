<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_number')->nullable()->comment('1 or 2 for semester system, null for yearly');
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['due','partial','paid','overdue'])->default('due');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_installments');
    }
};

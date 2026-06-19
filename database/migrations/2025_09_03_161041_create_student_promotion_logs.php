<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_promotion_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('cascade');

            $table->foreignId('target_academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('cascade');

            $table->integer('promoted_count')->default(0);
            $table->integer('total_count')->default(0);
            $table->integer('errors_count')->default(0);

            $table->foreignId('performed_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->timestamp('performed_at');
            $table->json('additional_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // ✅ حل مشكلة الاسم الطويل
            $table->index(['source_academic_year_id', 'target_academic_year_id'], 'promotion_years_idx');
            $table->index('performed_by');
            $table->index('performed_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_promotion_logs');
    }
};

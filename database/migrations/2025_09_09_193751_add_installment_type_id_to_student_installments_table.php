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
        Schema::table('student_installments', function (Blueprint $table) {
            $table->foreignId('installment_type_id')->nullable()->constrained('installment_types')->nullOnDelete()->after('student_enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_installments', function (Blueprint $table) {
            $table->dropForeign(['installment_type_id']);
            $table->dropColumn('installment_type_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_installments', function (Blueprint $table) {
            if (Schema::hasColumn('student_installments', 'kind')) {
                $table->dropColumn('kind');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_installments', function (Blueprint $table) {
            $table->string('kind')->nullable()->after('student_enrollment_id');
        });
    }
};

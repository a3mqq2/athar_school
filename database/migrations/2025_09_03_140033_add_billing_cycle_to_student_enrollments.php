<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_billing_cycle_to_student_enrollments.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->enum('billing_cycle', ['year','semester'])->default('year')->after('classroom_id');
        });
    }
    public function down(): void {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });
    }
};

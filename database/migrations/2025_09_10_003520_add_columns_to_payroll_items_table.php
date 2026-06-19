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
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('advance_deduction', 10, 2)->default(0)->after('deduction');
            $table->decimal('final_amount', 10, 2)->after('net_amount');
        });

        // remove treasury from payrolls table
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['treasury_id']);
            $table->dropColumn('treasury_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['advance_deduction', 'final_amount']);
            $table->foreignId('treasury_id')->constrained('treasuries')->after('employee_id');
        });
    }
};

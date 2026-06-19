<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_payments', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('cash')->after('amount');
            $table->foreignId('transaction_id')->nullable()->after('treasury_id')
                ->constrained('transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaction_id');
            $table->dropColumn('payment_method');
        });
    }
};

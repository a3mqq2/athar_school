<?php
// database/migrations/xxxx_create_user_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // الموظف
            $table->decimal('amount', 10, 2); // المبلغ
            $table->enum('type', ['credit', 'debit']); // دائن أو مدين
            $table->string('description'); // الوصف
            $table->string('reference_type')->nullable(); // نوع المرجع (salary, advance, adjustment)
            $table->unsignedBigInteger('reference_id')->nullable(); // معرف المرجع
            $table->unsignedBigInteger('transaction_id')->nullable(); // ربط مع المعاملة في الخزينة
            $table->unsignedBigInteger('created_by'); // من أنشأ المعاملة
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_transactions');
    }
};
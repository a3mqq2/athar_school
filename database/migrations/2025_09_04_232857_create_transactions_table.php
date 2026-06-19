<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payee_name'); // اسم المسلم
            $table->decimal('amount', 15, 2); // قيمة
            $table->text('description')->nullable(); // وصف
            $table->string('document_number')->nullable(); // رقم المستند
            $table->enum('transaction_type', ['deposit', 'withdrawal']); // نوع الحركة
            $table->foreignId('transaction_type_id')->constrained('transaction_types'); // تصنيف الحركة
            $table->foreignId('treasury_id')->constrained('treasuries'); // الخزينة
            $table->foreignId('user_id')->constrained('users'); // المستخدم الذي أدخل المعاملة
            $table->timestamps();
            
            $table->index(['treasury_id', 'transaction_type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
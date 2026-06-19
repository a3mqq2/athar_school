<?php
// database/migrations/xxxx_create_employee_advances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('treasury_id');
            $table->decimal('amount', 10, 2); // القيمة الإجمالية
            $table->date('advance_date'); // التاريخ
            $table->decimal('monthly_deduction', 10, 2); // الاستقطاع الشهري
            $table->decimal('remaining_amount', 10, 2); // المتبقي
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid'); // الحالة
            $table->text('description');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('treasury_id')->references('id')->on('treasuries')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['employee_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_advances');
    }
};
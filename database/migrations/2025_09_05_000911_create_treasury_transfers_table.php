<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('treasury_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_treasury_id')->constrained('treasuries')->comment('الخزينة المرسلة');
            $table->foreignId('to_treasury_id')->constrained('treasuries')->comment('الخزينة المستقبلة');
            $table->decimal('amount', 15, 2)->comment('المبلغ المنقول');
            $table->text('description')->nullable()->comment('وصف التحويل');
            $table->string('reference_number')->nullable()->comment('رقم مرجعي');
            $table->foreignId('user_id')->constrained('users')->comment('المستخدم الذي أجرى التحويل');
            $table->timestamps();
            
            $table->index(['from_treasury_id', 'to_treasury_id']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('treasury_transfers');
    }
};
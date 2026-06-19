<?php
// database/migrations/xxxx_add_employee_id_to_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('user_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('employee_id');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropIndex(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
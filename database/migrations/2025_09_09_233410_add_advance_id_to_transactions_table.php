<?php
// database/migrations/xxxx_add_advance_id_to_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('advance_id')->nullable()->after('employee_id');
            $table->foreign('advance_id')->references('id')->on('employee_advances')->onDelete('cascade');
            $table->index('advance_id');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['advance_id']);
            $table->dropIndex(['advance_id']);
            $table->dropColumn('advance_id');
        });
    }
};
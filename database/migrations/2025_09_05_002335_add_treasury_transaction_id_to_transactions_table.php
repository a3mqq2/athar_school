<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('treasury_transfer_id')->nullable()->constrained('treasury_transfers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['treasury_transfer_id']);
            $table->dropColumn('treasury_transfer_id');
        });
    }
};
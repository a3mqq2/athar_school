<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // إضافة العمود إذا لم يكن موجوداً
        if (!Schema::hasColumn('employee_advances', 'status')) {
            Schema::table('employee_advances', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('remaining_amount');
            });
        }

        // تنظيف البيانات الموجودة
        DB::table('employee_advances')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'active']);

        // تحديث السلف المكتملة
        DB::table('employee_advances')
            ->where('remaining_amount', '<=', 0)
            ->update(['status' => 'completed']);
    }

    public function down()
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
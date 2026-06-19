<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'salary')) {
                $table->decimal('salary', 12, 2)->nullable()->default(0)->after('hire_date');
            }
            if (Schema::hasColumn('users', 'job_title')) {
                $table->dropColumn('job_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'salary')) {
                $table->dropColumn('salary');
            }
            if (Schema::hasColumn('users', 'hire_date')) {
                $table->dropColumn('hire_date');
            }
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};

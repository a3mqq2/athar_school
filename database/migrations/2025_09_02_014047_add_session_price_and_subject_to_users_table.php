<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'subject')) {
                $table->string('subject')->nullable()->after('salary');
            }
            if (!Schema::hasColumn('users', 'session_price')) {
                $table->decimal('session_price', 12, 2)->nullable()->default(0)->after('subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'session_price')) {
                $table->dropColumn('session_price');
            }
            if (Schema::hasColumn('users', 'subject')) {
                $table->dropColumn('subject');
            }
        });
    }
};

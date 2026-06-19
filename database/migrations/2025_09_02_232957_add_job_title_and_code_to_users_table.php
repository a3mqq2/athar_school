<?php

// database/migrations/2025_09_03_000000_add_job_title_and_code_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('session_price');
            }
            if (!Schema::hasColumn('users', 'code')) {
                $table->string('code')->unique()->nullable()->after('job_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('users', 'job_title')) {
                $table->dropColumn('job_title');
            }
        });
    }
};

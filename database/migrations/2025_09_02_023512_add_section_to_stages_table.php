<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            if (!Schema::hasColumn('stages','section')) {
                $table->enum('section',['local','international'])->default('local')->after('name')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            if (Schema::hasColumn('stages','section')) {
                $table->dropColumn('section');
            }
        });
    }
};

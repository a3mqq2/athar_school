<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE student_installments MODIFY COLUMN status ENUM('due','partial','paid','overdue','refunded') NOT NULL DEFAULT 'due'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE student_installments MODIFY COLUMN status ENUM('due','partial','paid','overdue') NOT NULL DEFAULT 'due'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->enum('section_type', ['local','international'])->default('local');
            $table->foreignId('stage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2); 
            $table->timestamps();

            $table->unique(['section_type','stage_id','grade_id'], 'uniq_fee_scope_no_term');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};

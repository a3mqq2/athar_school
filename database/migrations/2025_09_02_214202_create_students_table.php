<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('parent_name')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'graduated', 'transferred', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

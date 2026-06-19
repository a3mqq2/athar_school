<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create sections table
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['local', 'international'])->default('local');
            $table->timestamps();
        });

        // Add section_id to stages table
        Schema::table('stages', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->after('id');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->index('section_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove foreign key and column from stages
        Schema::table('stages', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });

        // Drop sections table
        Schema::dropIfExists('sections');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assigned_subjects_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('subjects');
            $table->foreign('class_id')->references('id')->on('school_classes');
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assigned_subjects_classes');
    }
};

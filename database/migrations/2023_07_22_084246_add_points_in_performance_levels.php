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
        Schema::table('performance_levels', function (Blueprint $table) {
            $table->float('min_point')->nullable();
            $table->float('max_point')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('performance_levels', function (Blueprint $table) {
            $table->dropColumn('min_point');
            $table->dropColumn('max_point');
        });
    }
};

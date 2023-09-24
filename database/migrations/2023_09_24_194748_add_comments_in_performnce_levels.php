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
            $table->text('comment_1')->nullable();
            $table->text('comment_2')->nullable();
            $table->text('comment_3')->nullable();
            $table->text('comment_4')->nullable();
            $table->text('comment_5')->nullable();
            $table->text('comment_6')->nullable();
            $table->text('comment_7')->nullable();
            $table->text('comment_8')->nullable();
            $table->text('comment_9')->nullable();
            $table->text('comment_10')->nullable();
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
            $table->dropColumn('comment_1');
            $table->dropColumn('comment_2');
            $table->dropColumn('comment_3');
            $table->dropColumn('comment_4');
            $table->dropColumn('comment_5');
            $table->dropColumn('comment_6');
            $table->dropColumn('comment_7');
            $table->dropColumn('comment_8');
            $table->dropColumn('comment_9');
            $table->dropColumn('comment_10');
        });
    }
};

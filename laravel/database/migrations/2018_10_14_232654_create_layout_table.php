<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLayoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('layout', function (Blueprint $table) {
            $table->time('startTime')->default('10:00:00');
            $table->time('tempTime')->nullable();
            $table->integer('durationRound')->default('105');
            $table->integer('durationFinal')->default('120');
            $table->string('parameter1')->nullable();
            $table->string('parameter2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('layout', function (Blueprint $table) {
            $table->drop('layout');
        });
    }
}

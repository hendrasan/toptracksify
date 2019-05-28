<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            // type = daily / weekly / biweekly / monthly
            $table->string('type');
            // add number of tracks eg. top 10 / top 20 / top 40?
            $table->integer('number_of_tracks')->nullable();
            // add number of tracks eg. top 10 / top 20 / top 40?
            $table->timestamp('last_updated')->nullable();
            $table->tinyInteger('is_stopped')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charts');
    }
}

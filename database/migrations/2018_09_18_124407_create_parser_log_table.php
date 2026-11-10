<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parser_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parser_id');
            $table->string('hash', 32)->nullable();
            $table->string('url', 400)->nullable();
            $table->string('method', 4)->nullable();
            $table->text('params')->nullable();
            $table->tinyInteger('entity_type')->nullable();
            $table->integer('entity_id')->nullable();
            $table->tinyInteger('result');
            $table->text('message')->nullable();
            $table->date('date_added');
            $table->time('time_added');
            $table->index(['parser_id', 'hash']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parser_log');
    }
}

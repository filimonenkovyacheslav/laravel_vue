<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParserResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parser_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parser_id');
            $table->string('block', 255);
            $table->string('url', 255)->nullable();
            $table->tinyInteger('done')->default(0);
            $table->integer('parsed')->default(0);
            $table->timestamps();
            $table->unique(['parser_id', 'block']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parser_results');
    }
}

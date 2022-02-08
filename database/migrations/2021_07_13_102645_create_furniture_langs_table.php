<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFurnitureLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('furniture_langs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('furniture_id');
            $table->tinyInteger('lang_id');
            $table->string('title', 255);
            $table->string('address', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['furniture_id', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('furniture_langs');
    }
}

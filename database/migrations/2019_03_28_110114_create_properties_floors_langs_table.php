<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesFloorsLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties_floors_langs', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('floor_id');
			$table->tinyInteger('lang_id');
			$table->string('title', 225)->nullable();
			$table->text('description')->nullable();
			$table->timestamps();

			$table->index(['floor_id', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties_floors_langs');
    }
}

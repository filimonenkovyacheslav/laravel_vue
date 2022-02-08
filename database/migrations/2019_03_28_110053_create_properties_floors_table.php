<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesFloorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties_floors', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('property_id');

			$table->string('title', 225)->nullable();
			$table->text('description')->nullable();
			$table->integer('image')->nullable();

			$table->integer('bedrooms')->nullable();
			$table->integer('bathrooms')->nullable();

			$table->float('area_size_default')->nullable();
			$table->float('area_size_local')->nullable();
			$table->tinyInteger('area_size_measure')->nullable();

			$table->float('price_default')->nullable();
			$table->float('price_local')->nullable();
			$table->tinyInteger('currency_code')->nullable();

			$table->integer('sort_order');
			$table->timestamps();

			$table->index('property_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties_floors');
    }
}

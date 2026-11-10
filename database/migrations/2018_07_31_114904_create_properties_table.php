<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->increments('id');

			$table->string('title', 500);
            $table->string('slug', 500);

			$table->integer('author');
            $table->tinyInteger('status');
            $table->tinyInteger('label')->nullable();

			$table->float('price_default');
			$table->float('price_local')->nullable();
            $table->float('price_second')->nullable();
            $table->tinyInteger('currency_code');
            $table->string('price_before')->nullable();
            $table->string('price_after')->nullable();
            $table->boolean('price_hidden')->nullable();

			$table->text('description')->nullable();
			$table->tinyInteger('property_type')->nullable();
			$table->tinyInteger('property_subtype')->nullable();
			$table->tinyInteger('property_status')->nullable();
			$table->tinyInteger('property_rent_schedule')->nullable();

            $table->string('postal_code', 255)->nullable();
            $table->tinyInteger('country')->nullable();
            $table->string('state', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('neighborhood', 255)->nullable();
			$table->string('address', 255)->nullable();
			$table->string('map_address', 255)->nullable();
            $table->string('lat', 100)->nullable();
            $table->string('lng', 100)->nullable();

			$table->float('property_area_default')->nullable();
			$table->float('property_area_local')->nullable();
			$table->tinyInteger('property_area_measure')->nullable();

			$table->float('land_area_default')->nullable();
			$table->float('land_area_local')->nullable();
			$table->tinyInteger('land_area_measure')->nullable();

			$table->integer('garage')->nullable();
			$table->float('garage_area_default')->nullable();
			$table->float('garage_area_local')->nullable();
			$table->tinyInteger('garage_area_measure')->nullable();

			$table->integer('bedrooms')->nullable();
			$table->integer('bathrooms')->nullable();
			$table->string('year_built', 255)->nullable();

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
        Schema::dropIfExists('properties');
    }
}

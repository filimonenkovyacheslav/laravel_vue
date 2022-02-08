<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_entities', function (Blueprint $table) {
            $table->increments('id');
			$table->string('title', 500);
			$table->string('slug', 500);
			$table->integer('author');
			$table->tinyInteger('status');
			$table->text('description');
			$table->text('short_description');
			$table->tinyInteger('job_category_id');
			$table->tinyInteger('job_type');
			$table->string('company_name', 255);
			$table->tinyInteger('currency_code');
            $table->tinyInteger('label')->nullable();
			$table->float('price_default')->nullable();
			$table->float('price_local')->nullable();
            $table->float('price_second')->nullable();
            $table->string('price_before')->nullable();
            $table->string('price_after')->nullable();
            $table->boolean('price_hidden')->nullable();
			$table->tinyInteger('job_status')->nullable();
            $table->string('postal_code', 255)->nullable();
            $table->tinyInteger('country')->nullable();
            $table->string('state', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('neighborhood', 255)->nullable();
			$table->string('address', 255)->nullable();
			$table->string('map_address', 255)->nullable();
            $table->string('lat', 100)->nullable();
            $table->string('lng', 100)->nullable();
			$table->tinyInteger('job_salary_type')->nullable();
			$table->integer('photo')->nullable();
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
        Schema::dropIfExists('job_entities');
    }
}

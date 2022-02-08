<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_companies', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id');
			$table->tinyInteger('lang_id');
			$table->string('company_slug', 255)->nullable();
			$table->string('company_name', 255)->nullable();
			$table->string('license', 255)->nullable();
            $table->text('opening_hours')->nullable();
			$table->text('description')->nullable();
			$table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_companies');
    }
}

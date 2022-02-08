<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_categories', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('good_category_id');
			$table->integer('parent_id');
			$table->tinyInteger('lang_id');
			$table->string('name', 255);
			$table->string('slug', 255);
            $table->tinyInteger('status')->default('1');
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
        Schema::dropIfExists('good_categories');
    }
}

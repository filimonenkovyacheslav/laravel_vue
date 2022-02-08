<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('professions', function (Blueprint $table) {
    			$table->increments('id');
    			$table->integer('profession_id');
          $table->integer('parent_id');
    			$table->tinyInteger('lang_id');
    			$table->string('name', 255);
    			$table->string('slug', 255);
    			$table->integer('img_background')->nullable();
    			$table->integer('img_logo')->nullable();
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
        Schema::dropIfExists('professions');
    }
}

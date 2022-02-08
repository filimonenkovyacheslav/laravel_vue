<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_langs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('good_id');
            $table->tinyInteger('lang_id');
            $table->string('title', 255);
            $table->string('address', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['good_id', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_langs');
    }
}

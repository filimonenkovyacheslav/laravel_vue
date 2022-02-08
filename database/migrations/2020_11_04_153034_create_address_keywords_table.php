<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_keywords', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('key_id')->index();
            $table->tinyInteger('lang_id');
            $table->string('hash', 32)->index();
            $table->string('slug', 255);
			$table->string('keyword', 255);
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
        Schema::dropIfExists('address_keywords');
    }
}

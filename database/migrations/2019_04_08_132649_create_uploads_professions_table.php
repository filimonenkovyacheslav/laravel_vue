<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadsProfessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	 
	public function up()
    {
        Schema::create('uploads_professions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('profession_id');
            $table->integer('upload_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploads_professions');
    }
}

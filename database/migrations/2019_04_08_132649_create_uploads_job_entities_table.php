<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadsJobEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	 
	public function up()
    {
        Schema::create('uploads_job_entities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_entity_id');
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
        Schema::dropIfExists('uploads_job_entities');
    }
}

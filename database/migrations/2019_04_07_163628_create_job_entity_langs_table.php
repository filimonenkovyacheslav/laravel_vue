<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobEntityLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_entity_langs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_entity_id');
            $table->tinyInteger('lang_id');
            $table->string('title', 255);
            $table->string('address', 255)->nullable();
            $table->text('description')->nullable();
			$table->text('short_description')->nullable();
            $table->timestamps();
           // $table->index('job_entity_id', 'lang_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_entity_langs');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 500);
            $table->string('slug', 500);
            $table->integer('title_uploads_id')->nullable();
            $table->string('file_link', 500)->nullable();
            $table->integer('author');
            $table->tinyInteger('status');
            $table->tinyInteger('label')->nullable();    
            $table->text('description')->nullable();
            $table->integer('position')->nullable();
            
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
        Schema::dropIfExists('news');
    }
}

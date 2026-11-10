<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFranchisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('franchises', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 500);
            $table->string('slug', 500);

            $table->integer('author');
            $table->tinyInteger('status');
            $table->tinyInteger('label')->nullable();
            
            $table->text('description')->nullable();
            $table->string('founded', 500)->nullable();
            $table->string('fee', 500)->nullable();
            $table->string('investment', 500)->nullable();
            $table->string('terms', 500)->nullable();

            $table->tinyInteger('country')->nullable();
            $table->string('state', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('map_address', 255)->nullable();
            $table->string('lat', 100)->nullable();
            $table->string('lng', 100)->nullable();

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
        Schema::dropIfExists('franchises');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->integer('media')->nullable();
            $table->string('url', 255);
            $table->tinyInteger('role_id');
            $table->string('map_address', 255)->nullable();
            $table->string('lat', 100)->nullable();
            $table->string('lng', 100)->nullable();
            $table->tinyInteger('country')->nullable();
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('street', 100)->nullable();
            $table->string('house', 100)->nullable();
            $table->timestamps();
            $table->index(['role_id', 'country']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_users');
    }
}

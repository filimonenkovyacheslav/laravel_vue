<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
    
            $table->string('title', 500);
            $table->string('slug', 500);
    
            $table->integer('author');
            $table->tinyInteger('status');
            $table->tinyInteger('label')->nullable();
            $table->integer('position')->nullable();
    
            $table->float('price_default');
            $table->float('price_local')->nullable();
            $table->float('price_second')->nullable();
            $table->tinyInteger('currency_code');
            $table->string('price_before')->nullable();
            $table->string('price_after')->nullable();
            $table->boolean('price_hidden')->nullable();
            $table->text('description')->nullable();
            
            $table->string('house', 50)->nullable();
            $table->string('street', 100)->nullable();
            $table->string('suburb', 100)->nullable();
            $table->string('region', 100)->nullable();

            $table->string('postal_code', 255)->nullable();
            $table->tinyInteger('country')->nullable();
            $table->string('state', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('neighborhood', 255)->nullable();
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
        Schema::dropIfExists('products');
    }
}

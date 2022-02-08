<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_domains', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('partner_id');
            $table->string('domain', 10);
            $table->string('title', 50)->nullable();
            $table->string('name', 50)->nullable();
            $table->integer('logo')->nullable();
            $table->string('url', 255)->nullable();
            $table->timestamps();
            $table->unique(['partner_id', 'domain']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_domains');
    }
}

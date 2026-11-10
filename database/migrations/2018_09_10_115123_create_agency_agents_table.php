<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgencyAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_agents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id');
            $table->integer('agent_id');
            $table->tinyInteger('status');
            $table->timestamps();
            $table->index('agency_id');
            $table->index('agent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agency_agents');
    }
}

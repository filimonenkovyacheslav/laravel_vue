<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('run_id');
            $table->integer('import_id')->nullable();
            $table->tinyInteger('entity_type')->default(0);
            $table->integer('entity_id')->default(0);
            $table->tinyInteger('result')->default(0);
            $table->text('message')->nullable();
            $table->date('date_added');
            $table->time('time_added');
            $table->index('run_id');
            $table->index('entity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_log');
    }
}

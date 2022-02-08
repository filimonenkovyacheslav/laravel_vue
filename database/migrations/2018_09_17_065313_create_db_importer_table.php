<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDbImporterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_importer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('old_id');
            $table->string('item_type', 255);
            $table->string('item_subtype', 255)->nullable();
			$table->integer('new_id');
			$table->string('old_attachment_link', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('db_importer');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_ids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('import_id');
            $table->tinyInteger('entity_type');
            $table->integer('entity_id');
            $table->timestamps();
            $table->unique(['user_id', 'entity_type', 'import_id']);
            $table->unique(['user_id', 'entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_ids');
    }
}

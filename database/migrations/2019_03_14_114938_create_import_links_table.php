<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('author');
            $table->string('link', 500);
            $table->tinyInteger('status')->default(0);
            $table->timestamp('status_time')->nullable();
            $table->integer('run_id')->default(0);
            $table->timestamps();
            $table->index('author');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_links');
    }
}

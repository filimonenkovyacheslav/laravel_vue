<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_runs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_id');
            $table->tinyInteger('status')->default(0);
            $table->date('run_date');
            $table->time('run_time');
            $table->timestamp('ended')->nullable();
            $table->integer('cnt_inserted')->default(0);
            $table->integer('cnt_updated')->default(0);
            $table->integer('cnt_deleted')->default(0);
            $table->integer('files_added')->default(0);
            $table->integer('files_deleted')->default(0);
            $table->integer('cnt_errors')->default(0);
            $table->index('link_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_runs');
    }
}

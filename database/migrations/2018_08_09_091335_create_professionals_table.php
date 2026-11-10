<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfessionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('professionals', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id');
			$table->tinyInteger('lang_id');
			$table->string('company_name', 255)->nullable();
			$table->string('position', 255)->nullable();
			$table->string('license', 255)->nullable();            
			$table->text('description')->nullable();
            $table->string('monday', 100)->nullable();
            $table->string('tuesday', 100)->nullable();
            $table->string('wednesday', 100)->nullable();
            $table->string('thursday', 100)->nullable();
            $table->string('friday', 100)->nullable();
            $table->string('saturday', 100)->nullable();
            $table->string('sunday', 100)->nullable();
            $table->string('holiday', 100)->nullable();
            $table->text('services')->nullable();
			$table->timestamps();
			$table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('professionals');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfessionsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('professions_users', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id');
			$table->tinyInteger('profession_id');
            $table->index(['user_id', 'profession_id']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::dropIfExists('professions_users');
    }
}

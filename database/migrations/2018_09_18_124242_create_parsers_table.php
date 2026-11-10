<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parsers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('model', 20);
			$table->string('url', 255);
			$table->tinyInteger('status')->default(0);
			$table->integer('log_id')->nullable();
			$table->timestamp('status_time')->nullable();
			$table->timestamp('last_start')->nullable();
			$table->timestamp('last_end')->nullable();
			$table->integer('last_result')->nullable();
			$table->integer('all_results')->default(0);
		});

		$parsers = [
			['model' => 'Yelp','url' => 'https://www.yelp.com'],
		];

		foreach($parsers as $p) {
			$parser = new Parser();
			$parser->fill($p);
			$parser->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('parsers');
	}
}

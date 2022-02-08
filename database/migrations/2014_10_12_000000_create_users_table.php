<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('role_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->tinyInteger('status');
            $table->tinyInteger('label')->nullable();

            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('slug', 255);
            $table->integer('photo')->nullable();
			$table->integer('header_media')->nullable();

            $table->string('house', 50)->nullable();
            $table->string('street', 100)->nullable();
            $table->string('suburb', 100)->nullable();
            $table->string('region', 100)->nullable();

            $table->string('postal_code', 255)->nullable();
            $table->string('state', 255)->nullable();

            $table->string('address', 255)->nullable();
            $table->string('map_address', 255)->nullable();
            $table->string('lat', 255)->nullable();
            $table->string('lng', 255)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
			$table->string('language', 255)->nullable();

			$table->string('phone', 255)->nullable();
			$table->string('tax_number', 255)->nullable();
			$table->string('fax_number', 255)->nullable();
			$table->string('mobile', 255)->nullable();


			$table->string('skype', 255)->nullable();
			$table->string('website', 255)->nullable();
			$table->string('facebook', 255)->nullable();
			$table->string('twitter', 255)->nullable();
			$table->string('linkedin', 255)->nullable();
			$table->string('instagram', 255)->nullable();
			$table->string('google_plus', 255)->nullable();
			$table->string('youtube', 255)->nullable();
			$table->string('pinterest', 255)->nullable();
			$table->string('vimeo', 255)->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

		// Default Admin User
		$user = new User;
		$userData = [
			'role_id' => 1,
			'name' => 'admin',
			'email' => 'admin@gmail.com',
			'password' => bcrypt('admin'),
			'status' => 1,
			'first_name' => 'Admin',
			'last_name' => 'Admin',
			'slug' => 'admin',
			'remember_token' => str_random(10),
		];

		foreach($userData as $k => $v) {
			$user->$k = $v;
		}
		$user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

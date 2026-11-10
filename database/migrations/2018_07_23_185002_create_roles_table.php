<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
		$roles = [
			['name' => 'administrator', 'title' => 'Administrator', 'description' => ''],
			/*['name' => 'agent', 'title' => 'Agent', 'description' => ''],
			['name' => 'agency', 'title' => 'Agency', 'description' => ''],
			['name' => 'architect_firm', 'title' => 'Architect Firm', 'description' => ''],
			['name' => 'architect', 'title' => 'Architect', 'description' => ''],
			['name' => 'building_company', 'title' => 'Building Company', 'description' => ''],
            ['name' => 'design_company', 'title' => 'Design Company', 'description' => ''],
			['name' => 'building_company_agent', 'title' => 'Building Company Agent', 'description' => ''],			
			['name' => 'project_home_company', 'title' => 'Project Home Company', 'description' => ''],
			['name' => 'project_home_company_agent', 'title' => 'Project Home Company Agent', 'description' => ''],
			['name' => 'property_management', 'title' => 'Property Management', 'description' => ''],
			['name' => 'vacation_home_company', 'title' => 'Vacation Home Company', 'description' => ''],*/
            ['name' => 'professional', 'title' => 'Professionals', 'description' => ''],
			['name' => 'seller', 'title' => 'Seller (Marketplace)', 'description' => ''],
			/*['name' => 'user', 'title' => 'User', 'description' => ''],*/
            /*['name' => 'artist', 'title' => 'Artist (Art)', 'description' => ''],
            ['name' => 'gallery', 'title' => 'Gallery (Art)', 'description' => ''],*/
            ['name' => 'brand', 'title' => 'Business (Brands, Products)', 'description' => ''],
		];

		foreach($roles as $r) {
			$role = new Role();
			$role->fill($r);
			$role->save();
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}

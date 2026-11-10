<?php

use Illuminate\Database\Seeder;
use App\Http\Models\Agencies\Agency;
use App\Http\Models\Tags\Profession;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//		$this->call([
//            UsersTableSeeder::class,
//        	PropertiesTableSeeder::class,
//        	FeaturesTableSeeder::class,
//        ]);
//
//		$agency = new Agency([
//			'user_id' => 1,
//			'lang_id' => 37,
//			'name' => 'Test Agency',
//		]);
//		$agency->save();
//
//		$agency = new Agency([
//			'user_id' => 1,
//			'lang_id' => 216,
//			'name' => 'Тестовое Агенство',
//		]);
//		$agency->save();
//
//		$profession = new Profession();
//		$profession->profession_id = 15;
//		$profession->lang_id = 37;
//		$profession->name = 'Carpenter';
//		$profession->save();
//
//		$profession = new Profession();
//		$profession->profession_id = 15;
//		$profession->lang_id = 216;
//		$profession->name = 'Плотник';
//		$profession->save();

		for($i = 2; $i <= 40; $i++) {
			$subQuery = 'insert into "properties" ("property_id","lang_id","title","slug","author","status","price_default","price_local","price_second","currency_code",
"price_before","price_after","price_hidden","description","property_type","property_subtype","property_status","property_rent_schedule","postal_code",
"country","state","city","neighborhood","address","map_address","lat","lng","property_area_default","property_area_local","property_area_measure",
"land_area_default","land_area_local","land_area_measure","garage","garage_area_default","garage_area_local","garage_area_measure","bedrooms","bathrooms",
"year_built","video")
select '. $i .',"lang_id",concat("title", \' \', '. $i .'),concat("slug", \'-\', '. $i .'),"author","status",(select floor(random() * 10000 + 1)::int),"price_local","price_second","currency_code",
"price_before","price_after","price_hidden","description","property_type","property_subtype","property_status","property_rent_schedule","postal_code",
"country","state","city","neighborhood","address","map_address","lat","lng","property_area_default","property_area_local","property_area_measure",
"land_area_default","land_area_local","land_area_measure","garage","garage_area_default","garage_area_local","garage_area_measure","bedrooms","bathrooms",
"year_built","video"
from "properties"
where "property_id" = 1';
			DB::insert($subQuery);
		}
    }
}

<?php

use Illuminate\Database\Seeder;
use \App\Http\Models\Properties\Property;

class PropertiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$properties = [
			[
				'property_id' => 1, 'lang_id' => 37,
				'title' => 'Test Property', 'slug' => 'test-property',
				'author' => 1, 'status' => 1,
				'price_local' => 1000, 'price_default' => 500, 'currency_code' => 840,
				'property_status' => 1,
			],
			[
				'property_id' => 1, 'lang_id' => 216,
				'title' => 'Тестовая Недвижимость', 'slug' => 'test-property',
				'author' => 1, 'status' => 1,
				'price_local' => 1000, 'price_default' => 500, 'currency_code' => 840,
				'property_status' => 1,
			],
			[
				'property_id' => 2, 'lang_id' => 37,
				'title' => 'Test Property 2', 'slug' => 'test-property-2',
				'author' => 1, 'status' => 1,
				'price_local' => null, 'price_default' => 5000, 'currency_code' => 840,
				'property_status' => 1,
			],
			[
				'property_id' => 2, 'lang_id' => 216,
				'title' => 'Тестовая Недвижимость 2', 'slug' => 'test-property-2',
				'author' => 1, 'status' => 1,
				'price_local' => null, 'price_default' => 5000, 'currency_code' => 840,
				'property_status' => 1,
			],
		];

		foreach($properties as $p) {
			$property = new Property();
			$property->property_id = $p['property_id'];
			$property->lang_id = $p['lang_id'];
			$property->title = $p['title'];
			$property->slug = $p['slug'];
			$property->author = $p['author'];
			$property->status = $p['status'];
			$property->price_local = $p['price_local'];
			$property->price_default = $p['price_default'];
			$property->currency_code = $p['currency_code'];
			$property->property_status = $p['property_status'];
			$property->save();
		}
    }
}

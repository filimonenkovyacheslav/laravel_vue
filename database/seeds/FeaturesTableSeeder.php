<?php

use Illuminate\Database\Seeder;
use App\Http\Models\Tags\Feature;

class FeaturesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$items = [
			['name' => 'Air Conditioning',],
			['name' => 'Alarm System',],
			['name' => 'Alfresco',],
			['name' => 'Automatic Watering System',],
			['name' => 'Balcony',],
			['name' => 'Bar',],
			['name' => 'Basement/Cellar',],
			['name' => 'Basketball Court',],
			['name' => 'BBQ',],
			['name' => 'Boat Dock',],
			['name' => 'Central Heating',],
			['name' => 'Central Vacuum System',],
			['name' => 'Concierge/Doorman',],
			['name' => 'Conservatory',],
			['name' => 'Double Glazing',],
			['name' => 'Electric Gate',],
			['name' => 'External Lighting System',],
			['name' => 'Fireplace',],
			['name' => 'Games Room',],
			['name' => 'Garage',],
			['name' => 'Garden',],
			['name' => 'Guest House',],
			['name' => 'Gym',],
			['name' => 'Hamam',],
			['name' => 'Helipad',],
			['name' => 'Home Theatre',],
			['name' => 'Integrated Music Sound System',],
			['name' => 'Jacuzzi',],
			['name' => 'Laundry',],
			['name' => 'Library',],
			['name' => 'Lift',],
			['name' => 'Outbuildings',],
			['name' => 'Pantry',],
			['name' => 'Parking',],
			['name' => 'Pond',],
			['name' => 'Pool House',],
			['name' => 'Putting Green',],
			['name' => 'Restaurant',],
			['name' => 'Roof Top Terrace',],
			['name' => 'Sauna',],
			['name' => 'Security',],
			['name' => 'Security doors',],
			['name' => 'Security System',],
			['name' => 'Security windows',],
			['name' => 'Separate Apartment',],
			['name' => 'Shutters',],
			['name' => 'Smart Home System',],
			['name' => 'Spa',],
			['name' => 'Squash Court',],
			['name' => 'Staff Quarters',],
			['name' => 'Storage',],
			['name' => 'Study/Office',],
			['name' => 'Swimming Pool',],
			['name' => 'Tennis Court',],
			['name' => 'Terrace',],
			['name' => 'Underfloor Heating',],
			['name' => 'Walk-in closet',],
			['name' => 'Wine Cellar',],
		];

		$iter = 1;
		foreach($items as $item) {
			$feature = new Feature();
			$feature->feature_id = $iter;
			$feature->lang_id = 37;
			$feature->name = $item['name'];
			$feature->save();
			$iter++;
		}
		$iter = 1;
		foreach($items as $item) {
			$feature = new Feature();
			$feature->feature_id = $iter;
			$feature->lang_id = 216;
			$feature->name = $item['name'];
			$feature->save();
			$iter++;
		}
    }
}

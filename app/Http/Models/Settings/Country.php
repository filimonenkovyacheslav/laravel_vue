<?php

namespace App\Http\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Property;

class Country extends Model
{
	public $fillable = [
		'name', 'iso2', 'iso3'
	];

	public function properties(){
        return $this->hasMany(Property::class, 'country');
    }

	public static function getCoyntriesForSelect()
	{
		$countries = [];
		foreach(static::all() as $i => $country) {
			$countries[$country->id] = $country->name;
		}
		return $countries;
	}
	public static function getCountryCodes($field = 'iso2', $reverse = false)
	{
		$countries = [];
		$key = $reverse ? $field : 'id';
		$value = $reverse ? 'id' : $field;
		foreach(static::all()->toArray() as $i => $country) {
			$countries[$country[$key]] = $country[$value];
		}
		return $countries;
	}
    
    public static function getCountryName($id)
    {
        $country = static::find($id);
        return $country ? $country->name : '';
    }

    public static function getCountryIdByName($name)
    {
        if (empty($name)) return 0;
        $id = static::where('name', '=', $name)->value('id');
        return $id ? $id : 0;
    }
    
    public static function getCountryId($iso, $field = 'iso2')
    {
        return static::where($field, $iso)->value('id');
    }
    
    public static function searchCountries($keyword)
    {
        $countries = static::where('name', 'ilike', '%'.$keyword.'%')->limit(15)->get();
        $results = [];
        if($countries) {
            foreach($countries as $country) {
                $results[] = ['id' => $country->id, 'name' => $country->name];
            }
        }
        return $results;
    }
    
    public static function searchCountriesAsLocations($keyword, $limit = 15)
    {
        $countries = static::where('name', 'ilike', '%'.$keyword.'%')->limit($limit)->get();
        $results = [];
        if($countries) {
            foreach($countries as $country) {
                $results[] = ['id' => $country->id, 'name' => $country->name, 'iso2' => $country->iso2, 'iso3' => $country->iso3, 'lat' => null, 'lng' => null, 'city' => null, 'state' => null, 'country' => $country->id];
            }
        }
        return $results;
    }
}

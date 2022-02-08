<?php

namespace App\Http\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use DB;

class Rate extends Model
{
	public $timestamps = false;
	public $fillable = [
		'ccy_from', 'ccy_to', 'rate', 'date_added', 'time_added'
	];

	public static function setDateRate($from, $to, $value)
	{
		if(is_null($value) || empty($value)) return false;
		$dateRate = DB::raw('CURRENT_DATE');

		$rate = static::where([['ccy_from', $from], ['ccy_to', $to], ['date_added', $dateRate]])->first();
		if(!$rate) {
			$rate = static::create(['ccy_from' => $from, 'ccy_to' => $to, 'rate' => $value, 'date_added' => $dateRate, 'time_added' => DB::raw('CURRENT_TIME')]);
		} else if(empty($rate->rate)) {
			$rate->fill(['rate' => $value, 'time_added' => DB::raw('CURRENT_TIME')])->save();
		}
		return true;
	}

	public static function getDateRate($from, $to)
	{
		$rate = static::where([['ccy_from', $from], ['ccy_to', $to], ['date_added', DB::raw('CURRENT_DATE')]])->first();

		if(!$rate || empty($rate->rate)) {
			$maxDate = static::where([['ccy_from', $from], ['ccy_to', $to]])->max('date_added');
			//dd($maxDate);
			if($maxDate && !is_null($maxDate) && !empty($maxDate)) {
				$rate = static::where([['ccy_from', $from], ['ccy_to', $to], ['date_added', $maxDate]])->first();
			}
		}
		//dd($rate);
		return $rate ? floatval($rate->rate) : 0;
	}
}
<?php

namespace App\Http\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use BaseModel;
use CustomLaravelLocalization;
use DB;

class Quote extends Model
{
	public $fillable = [
		'lang_id', 'phrase'
	];

	public static function getQuotes($params) {
		$langId = CustomLaravelLocalization::getLocaleCode();
		$quotes = static::where('lang_id', $langId);

		$orderBy = 'd_date';

		foreach($params as $k => $v) {
			if(!empty($v)) {
				switch($k) {
					case 'phrase':
						$phrase = '%'.$v.'%';
						$quotes->where('phrase', 'ilike', $phrase);
					break;
					case 'order_by':
						$orderBy = $v;
						break;
					default:
						break;
				}
			}
		}
		switch($orderBy) {
			case 'a_date':
				$quotes->orderBy('id', 'asc');
				break;
			case 'd_date':
				$quotes->orderBy('id', 'desc');
				break;
			case 'phrase':
				$quotes->orderBy('phrase', 'asc');
			default:
				break;
		}
		//dd($quotes->toSql());
		$pagination = $quotes->paginate(BaseModel::$pagination);

		return $pagination;
	}

	public static function addQuote($phrase)
	{
		$langId = CustomLaravelLocalization::getLocaleCode();
		if(!isset($phrase) || empty($phrase) || static::where([['lang_id', $langId], ['phrase', $phrase]])->exists()) return;
		
		$quote = new Quote();
		$quote->lang_id = $langId;
		$quote->phrase = $phrase;
		$quote->save();
		return;
	}

	public static function deleteQuote($id)
	{
		if(is_numeric($id) && !empty($id)) {
			static::find($id)->delete();
		}
		return;
	}

	public static function searchQuotes($keyword)
	{
		$langId = CustomLaravelLocalization::getLocaleCode();
		$quotes = static::where('lang_id', $langId);
		if(strlen($keyword) < 1) return [];
		$phrase = '%'.$keyword.'%';
		$quotes = $quotes->where('phrase', 'ilike', $phrase)->limit(15)->get();

		$results = [];
		if($quotes) {
			foreach($quotes as $quote) {
				$results[] = ['id' => $quote->id, 'name' => $quote->phrase];
			}
		}

		return $results;
	}

}
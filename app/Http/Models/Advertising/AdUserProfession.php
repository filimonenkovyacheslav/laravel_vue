<?php

namespace App\Http\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use BaseModel;
use Upload;

class AdUserProfession extends Model
{
	protected $table = 'ad_users_professions';
	public $timestamps = false;

	public $fillable = [
		'ad_user_id', 'profession_id'
	];

	public static function getProfessionsList($adUserId) {
		$professions = static::where('ad_user_id', $adUserId)->pluck('profession_id');
		return $professions ? implode(',', $professions->toArray()) : '';
	}

	public static function saveProfessions($adUserId, $professions = '') {
		$professions = array_filter(explode(',', $professions));
		
		static::where('ad_user_id', $adUserId)->delete();

		if(!empty($professions)) {
			foreach ($professions as $p) {
				$item = new static;
				$item->fill(['ad_user_id' => $adUserId, 'profession_id' => $p]);
				$item->save();
			}
		}
	}
}
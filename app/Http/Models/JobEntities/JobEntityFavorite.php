<?php

namespace App\Http\Models\JobEntities;

use Illuminate\Database\Eloquent\Model;
use Auth;

class JobEntityFavorite extends Model
{
	public $timestamps = false;

	protected $table = 'job_entities_favorite';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'job_entity_id',
	];

	public static function toggleFavoriteJobEntity($params)
	{
		$favorite = false;
		$userId = Auth::user() ? Auth::user()->id : 0;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'job_entity_id' => $params['job_entity_id']];
			$favorite = (int) $params['make_favorite'];

			static::where($data)->delete();

			if($favorite) {
				static::insert($data);
			}
		}
		return $favorite;
	}

	public static function isJobEntityFeatured($id)
	{
		$userId = Auth::user() ? Auth::user()->id : 0;
		$isFavorite = false;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'job_entity_id' =>$id];
			$isFavorite = static::where($data)->first() !== null;
		}
		return $isFavorite;
	}
}

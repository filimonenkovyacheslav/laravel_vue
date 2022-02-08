<?php

namespace App\Http\Models\Designs;

use Illuminate\Database\Eloquent\Model;
use Auth;

class DesignFavorite extends Model
{
	public $timestamps = false;

	protected $table = 'designs_favorite';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'design_id',
	];

	public static function toggleFavorite($params)
	{
		$favorite = false;
		$userId = Auth::user() ? Auth::user()->id : 0;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'design_id' => $params['design_id']];
			$favorite = (int) $params['make_favorite'];

			static::where($data)->delete();

			if($favorite) {
				static::insert($data);
			}
		}
		return $favorite;
	}

	public static function isDesignFeatured($id)
	{
		$userId = Auth::user() ? Auth::user()->id : 0;
		$isFavorite = false;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'design_id' =>$id];
			$isFavorite = static::where($data)->first() !== null;
		}
		return $isFavorite;
	}
}

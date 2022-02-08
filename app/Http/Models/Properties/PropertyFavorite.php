<?php

namespace App\Http\Models\Properties;

use Illuminate\Database\Eloquent\Model;
use Auth;

class PropertyFavorite extends Model
{
	public $timestamps = false;

	protected $table = 'properties_favorite';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'property_id',
	];

	public static function toggleFavoriteProperty($params)
	{
		$favorite = false;
		$userId = Auth::user() ? Auth::user()->id : 0;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'property_id' => $params['property_id']];
			$favorite = (int) $params['make_favorite'];

			static::where($data)->delete();

			if($favorite) {
				static::insert($data);
			}
		}
		return $favorite;
	}

	public static function isPropertyFeatured($id)
	{
		$userId = Auth::user() ? Auth::user()->id : 0;
		$isFavorite = false;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'property_id' =>$id];
			$isFavorite = static::where($data)->first() !== null;
		}
		return $isFavorite;
	}
}

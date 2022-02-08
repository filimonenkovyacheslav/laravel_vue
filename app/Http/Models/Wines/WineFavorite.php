<?php

namespace App\Http\Models\Wines;

use Illuminate\Database\Eloquent\Model;
use Auth;

class WineFavorite extends Model
{
	public $timestamps = false;

	protected $table = 'wines_favorite';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'wine_id',
	];

	public static function toggleFavorite($params)
	{
		$favorite = false;
		$userId = Auth::user() ? Auth::user()->id : 0;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'wine_id' => $params['wine_id']];
			$favorite = (int) $params['make_favorite'];

			static::where($data)->delete();

			if($favorite) {
				static::insert($data);
			}
		}
		return $favorite;
	}

	public static function isWineFeatured($id)
	{
		$userId = Auth::user() ? Auth::user()->id : 0;
		$isFavorite = false;

		if(!empty($userId)) {
			$data = ['user_id' => Auth::user()->id, 'wine_id' =>$id];
			$isFavorite = static::where($data)->first() !== null;
		}
		return $isFavorite;
	}
}

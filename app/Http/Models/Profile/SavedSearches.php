<?php

namespace App\Http\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Request;
use Route;

class SavedSearches extends Model
{
	protected $table = 'saved_searches';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'user_id',
		'path',
		'params',
		'results',
	];

	public static function getUserSearches($userId = null)
	{
		$userId = !empty($userId) ? $userId : Auth::user()->id;
		$savedSearches = static::where('user_id', $userId)->get();
		$savedSearches = !empty($savedSearches) ? $savedSearches->toArray() : [];

		return $savedSearches;
	}

	public static function saveUserSearch($request)
	{
		$data = $request->except('_token');
		$data['user_id'] = Auth::user()->id;
		
		$search = new static;
		$search->fill($data);
		return $search->save();
	}

	public static function updateAllSearches()
	{
		$searches = static::all();
		$searches = !empty($searches) ? $searches->toArray() : [];

		foreach($searches as $k => $v) {
			$params = strpos($v['params'], '?') == 0 ? $v['params'] . '&data_only=1' : '?data_only=1';
			$request = Request::create($v['path'] . $params);
			$responce = Route::dispatch($request);
			$responceData = $responce->original->getData();

			if(!empty($responceData)) {
				$responceDataDecoded = (array) json_decode($responceData['params']);
				$resultIds = [];

				if(!empty($responceDataDecoded['entities'])) {
					$type = !empty($responceDataDecoded['entity_type']) ? $responceDataDecoded['entity_type'] : 'property';

					foreach($responceDataDecoded['entities'] as $entity) {
						$resultIds[] = $type == 'property' ? $entity->property_id : $entity->relation->user_id;
					}
					if(array_diff($resultIds, explode(',', $v['results']))) {
						// send notification to user
						static::where('id', $v['id'])->update(['results' => implode(',', $resultIds)]);
					}
				}
			}
		}
		echo 'Searches was updated!';
	}
}

<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use Validator;
use DB;

class AddressKeyword extends \App\Http\Models\BaseModel
{
	use Sluggable;
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'key_id', 'lang_id', 'slug', 'keyword', 'hash'
	];

	public static $tableName = 'address_keywords';
	public static $key = 'key_id';

	public function sluggable()
	{
		return [
			'slug' => [
				'source' => 'keyword'
			]
		];
	}

	public static $saveValidate = [
		'keyword' => 'required',
	];
	public static $translatable = [
		'keyword'
	];
	public static $selectable = [
		'key_id', 'slug', 'keyword'
	];

	private static $relativeModels = [
        'art' => 'ArtAddressKeyword',
        'product' => 'ProductAddressKeyword',
        'wine' => 'WineAddressKeyword',
        'furniture' => 'FurnitureAddressKeyword',
        'good' => 'GoodAddressKeyword',
        'design' => 'DesignAddressKeyword',
        'property' => 'PropertyAddressKeyword',
        'user' => 'UserAddressKeyword',
        'brand' => 'UserAddressKeyword',
	];

	public static function countKeywords() {
        return [
            [
                'title' => 'Keywords',
                'count' => static::query()->count()
            ]
        ];
    }

    public static function saveKeyword($request) {

    	$validator = Validator::make($request->all(), static::$saveValidate);
		if($validator->fails()) {
			return redirect()->back()->withInput()->withErrors($validator);
		}
		$data = $request->toArray();
		unset($data['lang_id']);
		$data['hash'] = md5($data['keyword']);
		if (empty($data['key_id'])) {
			$keyword = static::getEntities(['hash' => $data['hash']], false, false, true);
			if ($keyword) return $keyword;
		}
		return static::saveItem(null, $data, false);
    }

    public static function searchKeywords($type, $keyword) {
    	$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		//$langId = 11;
		$model = !is_null($type) && isset(static::$relativeModels[$type]) ? static::$relativeModels[$type] : false;
		//dd($type, static::$relativeModels[$type]);

		$keyword = str_replace(';', '', str_replace('\\', '', $keyword));
		$keyword = str_replace('(', '', $keyword);

		$searchField = $defLang == $langId ? 'kd.keyword' : 'COALESCE(kl.keyword, kd.keyword)';
		$select = 'kd.key_id, ' . ($defLang == $langId ? 'kd.keyword' : 'COALESCE(kl.keyword, kd.keyword) as keyword');
		$binding = [];

		if ($model) {
			$parts = array_unique(explode(' ', str_replace('  ', ' ', str_replace(',', ' ', $keyword))));
			$cnt = sizeof($parts) + 2;
			$regStr = '\m(' . implode('|', $parts) . ')\M';
			$select .= ', CASE WHEN ' .$searchField . ' ilike ' . DB::getPdo()->quote($keyword) .
				' THEN 0 ELSE ' . $cnt . '-(SELECT count(*) FROM regexp_matches(' . $searchField . ',' . DB::getPdo()->quote($regStr) . ",'ig')) END as sorter";
			/*$select .= ', CASE WHEN ' .$searchField . " ilike ? THEN 0 ELSE " . $cnt . '-(SELECT count(*) FROM regexp_matches(' . $searchField . ",?,'ig')) END as sorter";
			$binding[] = $keyword;
			$binding[] = $regStr;*/
		}					
		
		$query = static::from('address_keywords as kd')
			->select(DB::raw($select));
		
		if ($model) {
			//$query->whereRaw('kd.key_id IN (SELECT {$type}_id FROM ' . $model::$tableName . ' WHERE key_id = {$v})");
			$query->join($model::$tableName . ' as r', 'r.key_id', '=', 'kd.key_id')
				->join($model::$relativeTable . ' as t', 't.id', '=', 'r.' . $model::$relativeId)
				->where('t.status', 1);
			if ($type == 'brand') {
				$query->join('roles as rl', 'rl.id', '=', 't.role_id')
					->where('rl.name', $type);
			}
		}

		if ($defLang != $langId) {
			$query->leftJoin('address_keywords as kl', function ($join) use($langId){
           		$join->on('kl.key_id', '=', 'kd.key_id')->where('kl.lang_id', '=', $langId);
        	});
		}
		$query->where('kd.lang_id', $defLang);
		if ($model) {
			//$query->whereRaw($searchField . ' ~* ' . DB::getPdo()->quote($regStr))
			$query->whereRaw($searchField . " ~* ?",  [$regStr])
				->distinct()
				->orderBy('sorter')
				->orderBy('keyword');
		} else {
			//$query->whereRaw($searchField . ' ilike ' . DB::getPdo()->quote('%' . $keyword .'%'));
			$query->where($searchField, 'ilike', '%' . $keyword .'%');
		}
		//dd($query->limit(20)->toSql());
		$results = $query->limit(15)->get();
		//dd($query->toSql(), $results->toArray(), '%' . DB::getPdo()->quote($keyword) . '%', $defLang);
		return $results ? $results->toArray() : [];
	}

	public static function saveEntityKeywords($keywords, $entityId, $type) {
		$model = static::$relativeModels[$type];
		$key = $model::$relativeId;
		$model::where($key, $entityId)->delete();

		if(!empty($keywords)) {
            if (!is_array($keywords)) $keywords = [['key_id' => $keywords]];
            $saved = [];
			foreach ($keywords as $k) {
				$id = is_array($k) ? $k['key_id'] : $k;
				if (isset($saved[$id])) continue;
				$item = new $model;
				$item->fill([$key => $entityId, 'key_id' => $id]);
				$item->save();
				$saved[$id] = 1;
			}
		}
	}
	public static function saveEntitiesKeywords($keywords, $entityIds, $type) {
		foreach ($entityIds as $id) {
			static::saveEntityKeywords($keywords, $id, $type);
		}
	}

	public static function getEntityKeywords($entityId, $type, $idsOnly = false)
	{
		$model = static::$relativeModels[$type];
		$key = $model::$relativeId;
		$ids = $model::select('key_id')->where($key, $entityId)->pluck('key_id');
		if ($ids) {
			$ids = $ids->toArray();
			return $idsOnly ? $ids : static::getEntities(['whereIn' => ['key_id' => $ids]], false);
		}

		return [];
	}

}

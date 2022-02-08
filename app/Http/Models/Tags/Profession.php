<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;
use Upload;

class Profession extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'profession_id', 'lang_id', 'name', 'slug', 'img_logo', 'img_background', 'parent_id'
	];

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return [
			'slug' => [
				'source' => 'name'
			]
		];
	}

	public static $listRoute = 'user.profile.professions';
	public static $type = 'profession';
	public static $tableName = 'professions';
	public static $key = 'profession_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'profession_id', 'slug', 'name', 'img_logo', 'img_background', 'parent_id'
	];

	public static function getAllList() {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.profession_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM professions as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN professions as pl on (pl.profession_id=p.profession_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang.' ORDER BY p.parent_id';
		$entities = DB::select($query);

		//$entities = static::query()->orderBy('name')->get();
		$professions = [];
		foreach($entities as $p) {
			$professions[$p->profession_id] = $p->name;
		}
		//dd($entities, $professions);
		return $professions;
	}

	public static function getAllListParent() {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.profession_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM professions as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN professions as pl on (pl.profession_id=p.profession_id AND pl.lang_id='.$langId.')';
		}
		$query .= ' WHERE p.parent_id = 0 AND p.lang_id='.$defLang.' ORDER BY p.parent_id';
		$entities = DB::select($query);

		//$entities = static::query()->orderBy('name')->get();
		$professionCategories = [];
		foreach($entities as $p) {
			$professionCategories[$p->profession_id] = $p->name;
		}
		//dd($entities, $professions);
		return $professionCategories;
	}

	public static function getChildrenCategoriesByParentId($categoryId) {
		$query = 'SELECT * FROM professions WHERE profession_id = '.$categoryId;
		$entities = DB::select($query);
		return $entities;
	}

	public static function getChildrenCategoriesByCategoryId($categoryId) {
		$query = 'SELECT * FROM professions WHERE parent_id = '.$categoryId;
		$entities = DB::select($query);
		return $entities;
	}

	public static function getChildrenCategoriesIdByParentId($categoryId) {
		$query = 'SELECT profession_id FROM professions WHERE parent_id = '.$categoryId;
		$entities = DB::select($query);
		$jobParent = array();
		foreach($entities as $p) {
			$jobParent[] = $p->profession_id;
		}
		return $jobParent;
	}

	public static function getProfessionByUserId($userId) {
		$professionId = DB::table('professions_users')->select('profession_id')->where('user_id', '=', $userId)->pluck('profession_id')->toArray();
		$professionId = !empty($professionId[0]) ? $professionId[0] : '';
		return $professionId;
	}

	public static function getProfessionDefaultImg($profId) {
		$defImgEntity = DB::table('professions')->where('profession_id', (int)$profId)->get( ['img_background','img_logo'] )->first();
		$defImgArray = array();
		$defImgArray['img_logo'] = ( !empty($defImgEntity->img_logo) ) ? $defImgEntity->img_logo : '';
		$defImgArray['img_background'] = ( !empty($defImgEntity->img_background) ) ?  $defImgEntity->img_background : '';
		return $defImgArray;
	}

	public static function getProfessionsById($userId) {
		$professions = DB::table('professions')
			->where([
				['user_id', '=', $userId],
				['professions.lang_id', '=', static::getDefaultLang()],
			])
			->join('professions_users', 'professions_users.profession_id', '=', 'professions.profession_id')
			->join('users', 'professions_users.user_id', '=', 'users.id')
			->pluck('professions.profession_id');
		$professions = !empty($professions) ? $professions->toArray() : [];
		return $professions;
	}

	public static function saveProfessions($request, $professions = [], $user_id = 0) {
		if(empty($professions)) {
			$professions = array_filter(explode(',', $request->professions[0]));
			$user_id = $request->id;
		}

		ProfessionUser::where('user_id', $user_id)->delete();

		if(!empty($professions)) {
			foreach ($professions as $p) {
				$item = new ProfessionUser;
				$item->fill(['user_id' => $user_id, 'profession_id' => $p]);
				$item->save();
			}
		}
	}

	public static function prepareSaveItem($request, $preset = false, $langsData = []) {
		$data = !$preset ? $request->all() : $request;

		$data = Upload::attachUploads($data, $request, ['imgBackgroundNew']);
		$data['imgBackgroundNew'] = ( empty($data['imgBackgroundNew']) || $data['imgBackgroundNew'] === 'undefined') ? 0 : $data['imgBackgroundNew'];
		$data['img_background'] = ( empty($data['imgBackgroundNew']) ) ? $data['img_background'] : $data['imgBackgroundNew'];

		$data = Upload::attachUploads($data, $request, ['imgLogoNew']);
		$data['imgLogoNew'] = ( empty($data['imgLogoNew']) || $data['imgLogoNew'] === 'undefined') ? 0 : $data['imgLogoNew'];
		$data['img_logo'] = ( empty($data['imgLogoNew']) ) ? $data['img_logo'] : $data['imgLogoNew'];

		static::saveItem($request, $data);
	}

	public static function getProfessionsListWithUsersCount() {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		$name = ($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)');

		$query = 'select count(u.id) as users_count, p.profession_id, p.slug, p.parent_id, '.$name.' as name
from professions as p
inner join professions_users as pu on pu.profession_id = p.profession_id
inner join users as u on (u.id=pu.user_id)
inner join roles as r on (r.id=u.role_id)';
		if($defLang != $langId) {
			$query .= ' left join professions as pl on (pl.profession_id=p.profession_id and pl.lang_id='.$langId.')';
		}
		$query .= "
where r.name='professional' and u.status=1
and p.lang_id=".$defLang."
group by p.profession_id, p.slug, p.parent_id, ".$name.' order by 4';
		$professions = DB::select($query);

		$sortList = array();

		foreach ($professions as $prof) {
			$id = $prof->profession_id;
			$parent_id = $prof->parent_id;
			if ($parent_id == 0) {
				$sortList[$id] = array();
				$sortList[$id]['id'] = $prof->profession_id;
				$sortList[$id]['name'] = $prof->name;
				$sortList[$id]['slug'] = $prof->slug;
				$sortList[$id]['parent_id'] = $prof->parent_id;
				$sortList[$id]['users_count'] = $prof->users_count;
				$sortList[$id]['children'] = array();
			}
		}
		//dd($professions, $sortList);

		foreach ($professions as $prof) {
			$id = $prof->profession_id;
			$parent_id = $prof->parent_id;
			if ($parent_id > 0) {
				if ( empty($sortList[$parent_id]['id']) && empty($sortList[$parent_id]['name']) ) {
					$entity = static::getChildrenCategoriesByParentId($parent_id);
					//dd($professions,$entity,$parent_id);
					if (!empty($entity)) {
						$entity = $entity[0];
					}

					$sortList[$parent_id]['id'] = $entity->id;
					$sortList[$parent_id]['name'] = $entity->name;
					$sortList[$parent_id]['slug'] = $entity->slug;
					$sortList[$parent_id]['parent_id'] = $entity->parent_id;
					$sortList[$parent_id]['children'] = array();
				}
				$sortList[$parent_id]['children'][$id]['id'] = $prof->profession_id;
				$sortList[$parent_id]['children'][$id]['name'] = $prof->name;
				$sortList[$parent_id]['children'][$id]['slug'] = $prof->slug;
				$sortList[$parent_id]['children'][$id]['parent_id'] = $prof->parent_id;
				$sortList[$parent_id]['children'][$id]['users_count'] = $prof->users_count;
			}
		}

		return $sortList;
	}

	public static function _getFieldsList() {
		return [
			'profession_id' => [
				'index' => 'profession_id',
				'type' => 'hidden',
				'label' => __('Profession Id'),
				'value' => ['profession', 'profession_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Profession Title *'),
				'value' => ['profession', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['profession', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['profession', 'parent_id'],
			],
		];
	}
}

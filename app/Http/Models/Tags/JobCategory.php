<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;

class JobCategory extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'job_category_id', 'lang_id', 'name', 'slug', 'parent_id',
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

	public static $listRoute = 'user.profile.jobCategories';
	public static $type = 'jobCategory';
	public static $tableName = 'job_categories';
	public static $key = 'job_category_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'job_category_id', 'slug', 'name', 'parent_id',
	];

	public static function getAllList() {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.job_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM job_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN job_categories as pl on (pl.job_category_id=p.job_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang.' ORDER BY p.parent_id';
		$entities = DB::select($query);

		//$entities = static::query()->orderBy('name')->get();
		$jobCategories = [];
		foreach($entities as $p) {
			$jobCategories[$p->job_category_id] = $p->name;
		}
		//dd($entities, $professions);
		return $jobCategories;
	}

	public static function getChildrenCategoriesByParentId($categoryId) {
		$query = 'SELECT job_category_id FROM job_categories WHERE parent_id = '.$categoryId;
		$entities = DB::select($query);
		$jobParent = array();
		foreach($entities as $p) {
			$jobParent[] = $p->job_category_id;
		}
		return $jobParent;
	}

	public static function getCategoryParentId($categoryId) {
		$query = 'SELECT parent_id FROM job_categories WHERE job_category_id = '.$categoryId;
		$entities = DB::select($query);
		$jobParent = '';
		foreach($entities as $p) {
			$jobParent = $p->parent_id;
		}
		return $jobParent;
	}

	public static function getAllListParent() {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.job_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM job_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN job_categories as pl on (pl.job_category_id=p.job_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.parent_id = 0 AND p.lang_id='.$defLang.' ORDER BY 2';
		$entities = DB::select($query);

		//$entities = static::query()->orderBy('name')->get();
		$jobCategories = [];
		foreach($entities as $p) {
			$jobCategories[$p->job_category_id] = $p->name;
		}
		//dd($entities, $professions);
		return $jobCategories;
	}

	public static function getJobCategoriesById($userId) {
		$jobCategories = DB::table('job_categories')
			->where([
				['user_id', '=', $userId],
				['job_categories.lang_id', '=', static::getDefaultLang()],
			])
			// ->join('professions_users', 'professions_users.profession_id', '=', 'professions.profession_id')
			// ->join('users', 'professions_users.user_id', '=', 'users.id')
			->pluck('job_categories.job_category_id');
		$jobCategories = !empty($jobCategories) ? $jobCategories->toArray() : [];
		return $jobCategories;
	}

	public static function saveJobCategories($request, $jobCategories = [], $user_id = 0) {
		if(empty($jobCategories)) {
			$jobCategories = array_filter(explode(',', $request->jobCategories[0]));
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

	public static function _getFieldsList() {
		return [
			'job_category_id' => [
				'index' => 'job_category_id',
				'type' => 'hidden',
				'label' => __('Job Category Id'),
				'value' => ['jobCategory', 'job_category_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Job Category Title *'),
				'value' => ['jobCategory', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['jobCategory', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['jobCategory', 'parent_id'],
			],
		];
	}
}

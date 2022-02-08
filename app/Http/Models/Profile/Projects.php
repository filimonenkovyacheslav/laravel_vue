<?php

namespace App\Http\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use CustomLaravelLocalization;
use Upload;

class Projects extends \App\Http\Models\BaseModel
{
	public static $tableName = 'projects';

    public $fillable = [
		'user_id', 'lang_id', 'title', 'description', 'sort_order'
	];

	public static function getUserProjects($userId) {
		$langId = CustomLaravelLocalization::getLocaleCode();
		$projects = static::where(['user_id' => $userId, 'lang_id' => $langId])->orderBy('sort_order', 'asc')->get();
		$projects = !empty($projects) ? $projects->toArray() : [];
		foreach($projects as $k => $v) {
			$projects[$k]['uploadsList'] = Upload::getUploadedImages($v['id'], static::$tableName);
			$projects[$k]['uploads'] = Upload::getUploadedImages($v['id'], static::$tableName, true);
		}
		return $projects;
	}

	public static function saveUserProjects($request) {
		$data = $request->except('_token');
		$userId = $data['user_id'];
		$langId = CustomLaravelLocalization::getLocaleCode();
		$curProjectsIds = static::where(['user_id' => $userId, 'lang_id' => $langId])->pluck('id');
		$curProjectsIds = !empty($curProjectsIds) ? $curProjectsIds->toArray() : [];
		foreach($data['projects'] as $k => $p) {
			$projectItemId = (isset($p['id']) && !empty($p['id']) ? $p['id'] : null);
			$projectItem = static::findOrCreate($projectItemId);
			$projectData = array_merge($p, ['user_id' => $userId, 'lang_id' => $langId, 'sort_order' => ($k + 1)]);
			$new = is_null($projectItemId);
			if(!$new) {
				$index = array_search($projectItemId, $curProjectsIds);
				array_splice($curProjectsIds, $index, 1);
			}
			$projectItem->fill($projectData);
			$projectItem->save();
			$projectItemId = $projectItem->id;
			Upload::saveUploadedImages(isset($p['uploads']) ? $p['uploads'] : [], $projectItemId, static::$tableName);
		}
		if(!empty($curProjectsIds)) {
			static::whereIn('id', $curProjectsIds)->delete();
			//PropertiesProjectsLang::whereIn('project_id', $curProjectsIds)->delete();
		}
		return static::getUserProjects($userId);
	}
}

<?php

namespace App\Http\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use BaseModel;
use CustomLaravelLocalization;
use Setting;
use Upload;

class Page extends Model
{
	public $fillable = [
		'name', 'lang_id', 'content'
	];

	public static function getAll() {
		$pagination = static::where('lang_id', 0)->orderBy('id')->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($page) {
			return static::_afterGet($page);
		});
		return $pagination;
	}

	public static function getTitle($name) {
		return static::where([['name', $name], ['lang_id', 0]])->value('content');
	}

	public static function getContent($name, $langId = null) {
		if(!$langId) {
			$langId = CustomLaravelLocalization::getLocaleCode();
		}

		$content = static::where([['name', $name], ['lang_id', $langId]])->value('content');
		if(!$content) {
			$content = static::where([['name', $name], ['lang_id', BaseModel::getDefaultLang()]])->value('content');
		}

		return $content ? $content : '';
	}

	public static function saveContent($request) {
		$data = $request->except('_token');

		if(!isset($data['name']) || !isset($data['lang_id'])) return false;
		static::where([['name', $data['name']], ['lang_id', $data['lang_id']]])->delete();

		$result = static::create($data);

		$defLang = BaseModel::getDefaultLang();
		if($data['lang_id'] != $defLang) {
			$content = static::where([['name', $data['name']], ['lang_id', $defLang]])->value('id');
			if(!$content) {
				$data['lang_id'] = $defLang;
				static::create($data);
			}
		}
		return $result;
	}

	public static function getPageTranslations($name) {
		$results = static::where('name', $name)->where('lang_id', '>', 0)->orderBy('id')->get();
		return $results ? $results->toArray() : [];
	}

	public static function _afterGet($page)
	{
		if(!$page || empty($page)) return null;
		$page['count'] = static::where('name', $page->name)->where('lang_id', '>', 0)->count();

		return $page;
	}

	public static function getHomeAll() {
		$results = Setting::getValuesBySection('homepage', false);

		$domains = [];
		$groups = [];
		$lists = [];
		$g = 0;
		foreach($results as $domain => $data) {
			$domain = str_replace('domain_', '', $domain);
			$data = static::_afterGetHome($data);
			if ($domain == 'default' || strpos($domain, 'All') === 0) $domains[$domain] = $data;
			else {
				$imgId = (int) $data['main'];
				if (!empty($imgId)) {
					if (!isset($groups[$imgId])) {
						$g++; 
						$groups[$imgId] = $g;
						$lists[$g] = [];
						$domains[$g] = $data;
					}
					$gr = $groups[$imgId];
					$lists[$gr][] = preg_replace('/\d+/u', '', $domain);
				}
			}
		}
		if(!isset($domains['default'])) {
			$domains['default'] = static::_afterGetFooter([]);
		}
		foreach($groups as $imgId => $gr) $domains[$gr]['list'] = array_unique($lists[$gr]);

		return $domains;
	}

	public static function getHomeImage($domain) {
		$data = Setting::getRandomValueLike('homepage', 'domain_'.$domain.'%');
		$dataAll = Setting::getRandomValueLike('homepage', 'domain_All%');
		if(!$data || ($dataAll && rand(0, 1))) {
			$data = $dataAll;
		}
		if(!$data) {
			$data = Setting::getValue('homepage', 'default', '[]');
		}

		return static::_afterGetHome($data);
	}

	public static function saveHome($request) {
		$data = $request->except('_token');

		$default = [];

		if(isset($data['title']) && !empty($data['title'])) {
			$default['title'] = $data['title'];
		}
		if(isset($data['url']) && !empty($data['url'])) {
			$default['url'] = $data['url'];
		}
		$data = Upload::attachUploads($data, $request, ['main_id'], false);
		$data = Upload::attachUploads($data, $request, ['logo_id'], false);
		if(isset($data['main']) && !empty($data['main'])) {
			$default['main'] = $data['main'];
		}
		if(isset($data['logo']) && !empty($data['logo'])) {
			$default['logo'] = $data['logo'];
		}

		Setting::setValue('homepage', 'default', json_encode($default));

		$current = Setting::deleteSettings('homepage', 'domain_%', true);

		$domains = [];
		foreach($data as $name => $value) {
			if(strpos($name, 'domain_') === 0 && !is_null($value) && !empty($value)) {
				$endLocale = strpos($name, '_', 7);
				if($endLocale > 0) {
					$domain = substr($name, 7, $endLocale - 7);
					$domains[$domain][substr($name, $endLocale + 1)] = $value;
				}
			}
		}
		//dd($data, $domains);
		foreach($domains as $domain => $data) {
			$data = Upload::attachUploads($data, $request, ['domain_'.$domain.'_main_id'], false);
			$data = Upload::attachUploads($data, $request, ['domain_'.$domain.'_logo_id'], false);

			if(isset($data['domain_'.$domain.'_main'])) {
				$data['main'] = $data['domain_'.$domain.'_main'];
			} else if(isset($data['domain_main'])) {
				$data['main'] = $data['domain_main'];
			}
			if(isset($data['domain_'.$domain.'_logo'])) {
				$data['logo'] = $data['domain_'.$domain.'_logo'];
			} else if(isset($data['domain_logo'])) {
				$data['logo'] = $data['domain_logo'];
			}
			$isGroup = !empty($data['list']);
			$list = explode(',', $isGroup ? $data['list'] : $domain);
			unset($data['domain_'.$domain.'_main'], $data['domain_main'], $data['main_id'], $data['domain_'.$domain.'_logo'], $data['domain_logo'], $data['logo_id'], $data['list']);
			//Setting::setValue('homepage', 'domain_'.$domain, json_encode($data));
			foreach ($list as $d) {
				Setting::setValue('homepage', 'domain_'.$d.($isGroup ? $domain : ''), json_encode($data));
			}
		}
		return true;
	}

	public static function _afterGetHome($data) {
		$domain = is_array($data) ? $data : json_decode($data, true);

		if(!isset($domain['title'])) $domain['title'] = '';
		if(!isset($domain['url'])) $domain['url'] = '';
		if(!isset($domain['main'])) $domain['main'] = '';
		if(!isset($domain['logo'])) $domain['logo'] = '';

		$domain['main_image'] = empty($domain['main']) ? [] : Upload::getUploadById($domain['main']);
		$domain['logo_image'] = empty($domain['logo']) ? [] : Upload::getUploadById($domain['logo']);
	
		return $domain;
	}

	public static function getFooterAll() {
		$results = Setting::getValuesBySection('footer', false);

		$domains = [];
		$groups = [];
		$lists = [];
		$g = 0;
		foreach($results as $domain => $data) {
			$domain = str_replace('domain_', '', $domain);
			$data = static::_afterGetFooter($data);
			if ($domain == 'default' || strpos($domain, 'All') === 0) $domains[$domain] = $data;
			else {
				$imgId = (int) $data['main'];
				if (!empty($imgId)) {
					if (!isset($groups[$imgId])) {
						$g++; 
						$groups[$imgId] = $g;
						$lists[$g] = [];
						$domains[$g] = $data;
					}
					$gr = $groups[$imgId];
					$lists[$gr][] = preg_replace('/\d+/u', '', $domain); //$domain;
				}
			}

		}
		if(!isset($domains['default'])) {
			$domains['default'] = static::_afterGetFooter([]);
		}
		foreach($groups as $imgId => $gr) $domains[$gr]['list'] = array_unique($lists[$gr]);
//dd($domains);
		return $domains;
	}

	public static function _afterGetFooter($data) {
		$domain = is_array($data) ? $data : json_decode($data, true);

		if(!isset($domain['url'])) $domain['url'] = '';
		if(!isset($domain['main'])) $domain['main'] = '';
		$domain['main_image'] = empty($domain['main']) ? [] : Upload::getUploadById($domain['main']);

		return $domain;
	}

	public static function getFooterImage($domain) {
		//$domain = 'au';
		$data = Setting::getRandomValueLike('footer', 'domain_'.$domain.'%');
		$dataAll = Setting::getRandomValueLike('footer', 'domain_All%');
		if(!$data || ($dataAll && rand(0, 1))) {
			$data = $dataAll;
		}
		if(!$data) {
			$data = Setting::getValue('footer', 'default', '[]');
		}

		return static::_afterGetFooter($data);
	}

	public static function saveFooter($request) {
		$data = $request->except('_token');

		$default = [];

		if(isset($data['url']) && !empty($data['url'])) {
			$default['url'] = $data['url'];
		}

		$data = Upload::attachUploads($data, $request, ['main_id'], false);
		if(isset($data['main']) && !empty($data['main'])) {
			$default['main'] = $data['main'];
		}

		Setting::setValue('footer', 'default', json_encode($default));

		$current = Setting::deleteSettings('footer', 'domain_%', true);

		$domains = [];
		//dd($data, $domains);
		foreach($data as $name => $value) {
			if(strpos($name, 'domain_') === 0 && !is_null($value) && !empty($value)) {
				$endLocale = strpos($name, '_', 7);
				if($endLocale > 0) {
					$domain = substr($name, 7, $endLocale - 7);
					$domains[$domain][substr($name, $endLocale + 1)] = $value;
				}
			}
		}
		//dd($data, $domains);
		foreach($domains as $domain => $data) {
			$data = Upload::attachUploads($data, $request, ['domain_'.$domain.'_main_id'], false);

			if(isset($data['domain_'.$domain.'_main'])) {
				$data['main'] = $data['domain_'.$domain.'_main'];
			} else if(isset($data['domain_main'])) {
				$data['main'] = $data['domain_main'];
			}
			$isGroup = !empty($data['list']);
			$list = explode(',', $isGroup ? $data['list'] : $domain);
			unset($data['domain_'.$domain.'_main'], $data['domain_main'], $data['main_id'], $data['list']);
			foreach ($list as $d) {
				Setting::setValue('footer', 'domain_'.$d.($isGroup ? $domain : ''), json_encode($data));
			}
			
		}
		return true;
	}
}
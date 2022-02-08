<?php

namespace App\Http\Models\Parsers;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\DomCrawler\Crawler;
use App\Jobs\ParserJob;
use CustomLaravelLocalization;
use Parser;
use Setting;
use ParserLog;
use ParserResult;
use User;
use Role;
use Profession;
use Upload;
use Property;
use \App\Http\Plugins\RollingCurl;

class BaseParser extends Model
{
	public static $job = null;
	public static $locale = null;
	public static $supportedLocales = [];
	public static $parserId = 0;
	public static $parser = null;
	public static $userFirstName = '-';
	public static $userLastName = '-';
	public static $lastUnique = null;

	public static $curl = null;
	public static $url = '';
	public static $uploadTemp = '';
	public static $parsed = 0;

	public static $cookies = [];

	public static $smallTimeout = 2;
	public static $bigTimeout = 20;
	/*public static $maxRequests = 50;
	public static $reqCounter = 0;*/

	public static $maxCountErrors = 100;
	public static $errCounter = 0;
	public static $lastResult = true;

	public static $professions = [];
	
	public static function setUrl($url)
	{
		static::$url = $url;
	}
	public static function setCurl($curl)
	{
		static::$curl = $curl;
	}

	public static function setJob($job)
	{
		static::$job = $job;
	}

	public static function setParserId($id)
	{
		static::$parserId = $id;
	}

	public static function setParser($parser)
	{
		static::$parser = $parser;
	}

	public static function setTempPath($dir = '')
	{
		static::$uploadTemp = public_path(empty($dir) ? '/temp' : '/'.$dir);
	}

	public static function setLocale()
	{
		$locale = config('app')['default_lang'];
		static::$locale = $locale;
	}
	public static function setSupportedLocales()
	{
		static::$supportedLocales = CustomLaravelLocalization::getSupportedLocales();
	}

	public static function setLastUserUnique($email)
	{
		$unique = 0;
		$result = User::select('email')->where('email', 'like', $email.'%')->orderBy('id', 'desc')->first();
		if($result) {
			$part = str_replace($email, '', $result->email);
			$pos = strpos($part, '.');
			if($pos > 0) {
				$unique = (int)substr($part, 0, $pos);
			}
		}
		static::$lastUnique = $unique;
	}

	public static function doParse($id, $job = null)
	{
		ini_set('max_execution_time', 0);
		ini_set('memory_limit', '256M');

		static::setJob($job);
		static::setParserId($id);

		static::controlStopping();
		$parser = Parser::setStatus($id, 2);
		if(!$parser) {
			static::setError('Parser is not found or status is invalid.');
		}
		static::setParser($parser);
		static::setLocale();
		static::setSupportedLocales();

		$model = app($parser->model.'Parser');
		$result = $model::run($parser);

		$logId = $result ? false : ParserLog::getLastError($id);
		
		Parser::setStatus($id, $result ? 0 : 5, $logId ? ['log_id' => $logId] : []);
		static::doExit();
	}

	public static function controlStopping()
	{
		$parser = Parser::getParser(static::$parserId);
		if(!$parser) static::doExit();
		if($parser->status == 3 || $parser->status == 4) {
			Parser::setStatus($parser->id, 4, empty($result) ? [] : ['last_result' => $result]);
			static::doExit();
		}
		static::setParser($parser);
	}

	public static function getParams($name, $sector = 'parsers')
	{
		$settings = Setting::getValuesLike($sector, $name.'_%');
		$params = [];
		if($settings) {
			$len = strlen($name) + 1;
			foreach($settings as $param) {
				$params[substr($param->name, $len)] = json_decode($param->value);
			}
		}
		foreach(static::$parserParams as $param) {
			if(!isset($params[$param])) {
				static::setError('Not all necessary parameters are found');
			}
		}
		return $params;
	}

	public static function getUserAgents($sector = 'parsers')
	{
		$params = Setting::getValue('parsers', 'useragents');
		return $params ? json_decode($params) : [];
	}

	public static function getProxyList($sector = 'parsers')
	{
		$params = Setting::getValue('parsers', 'proxies');
		return $params ? json_decode($params) : [];
	}

	public static function getProxiesStatus()
	{
		$params = Setting::getValue('parsers', 'proxies');
		$proxies = $params ? json_decode($params) : [];
		$result = ['all' => sizeof($proxies), 'alive' => 0, 'list' => ''];
		//dd($params, $proxies);
		if(sizeof($proxies) > 0) {
			$list = '';
			$cnt = 0;
			$params = Setting::getValue('parsers', 'alive-proxies');
			$alive = $params ? json_decode($params) : [];
			
			foreach($proxies as $i => $proxy) {
				if(in_array($proxy, $alive)) {
					$cnt++;
					$list .= 'OK';
				}
				$list .= "\t".$proxy.PHP_EOL;
			}
			$result['alive'] = $cnt;
			$result['list'] = $list;
		}
		return $result;
	}

	public static function saveAliveProxies($proxies)
	{
		Setting::setValue('parsers', 'alive-proxies', json_encode($proxies));
	}
	public static function saveProxies($proxies)
	{
		if(!is_array($proxies)) return;

		$proxies = array_filter($proxies, function($proxy) {
			return !empty($proxy);
		});
		Setting::setValue('parsers', 'proxies', json_encode(array_values($proxies)));
	}
	public static function deleteDeadProxies()
	{
		$alive = Setting::getValue('parsers', 'alive-proxies');
		Setting::setValue('parsers', 'proxies', $alive);
	}

	public static function getRoles()
	{
		$roles = [];
		foreach(Role::all() as $role) {
			$roles[$role->name] = $role->id;
		}
		return $roles;
	}

	public static function prepareTempDir($dirName = '')
	{
		static::setTempPath($dirName);
		$dir = static::$uploadTemp;
		if(!file_exists($dir)) {
            $oldmask = umask(0);
			mkdir($dir, 0777);
            umask($oldmask);
			return true;
		}
		echo 'emptyTempDir=>'.$dir.PHP_EOL;
		if($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if(is_file($dir.'/'.$file)) {
					@unlink($dir.'/'.$file);
				}
			}
			closedir($handle);
		}
		return true;
	}
	
	/*public static function setCookies($http_response_header)
	{
		foreach($http_response_header as $s)
		{
			if (preg_match('|^Set-Cookie:\s*([^=]+)=([^;]+);(.+)$|', $s, $parts))
			{
				static::$cookies[$parts[1]] = $parts[2];
			}
		}
	}*/

	public static function filterContent($html, $filter = 'body', $alternative = '')
	{
		$crawler = new Crawler($html);
		$nodes = $crawler->filter($filter);
		if($nodes->count() == 0 && !empty($alternative)) {
			echo 'alternative';
			$nodes = $crawler->filter($alternative);
		}

		return $nodes;
	}
	public static function getUrlParts($url, $log = []) {
		$parts = explode('?', $url, 2);
		$log['url'] = $parts[0];
		if(sizeof($parts) > 1) {
			$log['params'] = $parts[1];
		}
		return $log;
	}

	public static function controlRequestResult($info, $request)
	{
		if($info['http_code'] == 200)
		{
			static::$lastResult = true;
			return true;
		}
		$proxy = $request->options[CURLOPT_PROXY];
		$error = $info['http_code'];

		if(static::$lastResult) {
			static::$errCounter = 1;
			static::$lastResult = false;
		} else {
			static::$errCounter++;
		}
		$log = static::getUrlParts($request->url);
		
		//list($log['url'], $log['params']) = explode('?', $request->url, 2);
		static::setError('HTTP Code: '.$error.' PROXY: '.$proxy.' UserAgent:'.$request->options[CURLOPT_USERAGENT], (static::$errCounter >= static::$maxCountErrors), $log);

		return false;
	}

	public static function getNodeText($dom, $filter, $html = true)
	{
		$node = $dom->filter($filter);
		return $node->count() ? trim($html ? str_replace('<br>', ', ', $node->html()) : $node->text()) : null;
	}

	public static function updateResults($result = 1)
	{
		Parser::updateResults(static::$parser, $result);
		static::controlStopping();
	}

	public static function setError($message, $end = true, $data = [])
	{
		$id = static::$parserId;
		$logId = ParserLog::saveError($id, $message, $data);
		if($end) {
			Parser::setStatus($id, 5, ['log_id' => $logId]);
			static::doExit();
		}
	
		return true;
	}

	public static function saveLog($data, $update = true)
	{
		$data['parser_id'] = static::$parserId;
		ParserLog::saveData($data);
		if($update) {
			static::updateResults();
		}
		return true;
	}

	/*public static function getParsedResults($done = 1)
	{
		$results = ParserResult::getByParser(static::$parserId, $done);
		$parsed = [];
		foreach($results as $data) {
			$parsed[$data['block']] = ['done' => $data['done'], 'url' => $data['url'], 'page' => $data['page']];
		}
		return $parsed;
	}*/
	public static function getParsedResults($block)
	{
		return ParserResult::getParserResults(static::$parserId, $block);
	}

	public static function getParserResult($block) {
		return ParserResult::getParserResult(static::$parserId, $block);
	}

	public static function setParsedResults($block, $data)
	{
		ParserResult::setResult(static::$parserId, $block, $data);
		return true;
	}

	public static function getHash($log)
	{
		$str = '';
		foreach(static::$hashFields as $field) {
			$str .= $log[$field] ? $log[$field] : '';
		}
		return md5($str);
	}
	public static function isParsed(&$log, $type, $parserId = null)
	{
        $parserId = $parserId ? $parserId : static::$parserId;
		$log['hash'] = static::getHash($log);
		return ParserLog::isParsed($log['hash'], $type, $parserId);
	}

	public static function saveNewUser($data, $log, $profs = [], $updateResults = true)
	{
		print_r($log);
		/*if(!isset($data['name'])) {
			$username = substr(Str::slug($firstName . "-" . $lastName), 0, 20);
			$userRows  = User::whereRaw("name REGEXP '^{$username}(-[0-9]*)?$'")->get();
			$countUser = count($userRows) + 1;
			$data['name'] = ($countUser > 1) ? "{$username}-{$countUser}" : $username;
		}*/
		if(is_null(static::$lastUnique)) {
			static::setLastUserUnique(static::$userEmail);
		}
		$unique = static::$lastUnique + 1;
		if(!isset($data['email'])) {
			$data['email'] = static::$userEmail.$unique.'.com';
		}
		if(!isset($data['name'])) {
			$data['name'] = static::$userName;
		}
		if(!isset($data['password'])) {
			$data['password'] = 1111;
		}
		if(!isset($data['first_name'])) {
			$data['first_name'] = static::$userFirstName;
		}
		if(!isset($data['last_name'])) {
			$data['last_name'] = static::$userLastName;
		}
		if(!isset($data['status'])) {
			$data['status'] = 1;
		}
		if(!isset($data['role_id'])) {
			$data['role_id'] = 14;
			$data['role_name'] = 'user';
		}

		$langsData = [];
		$company = isset($data['company_name']) ? $data['company_name'] : null;
		$defLang = static::$locale;
		$defDesc = null;

		if(isset($data['descriptions'])) {
			$supportedLangs = static::$supportedLocales;
			
			foreach($data['descriptions'] as $lang => $desc) {
				if(isset($supportedLangs[$lang])) {
					$langsData[$supportedLangs[$lang]['code']] = ['description' => $desc, 'company_name' => $company];
				}
				if(is_null($defDesc)) {
					$defDesc = $desc;
				}
			}
		}
		if(sizeof($langsData) > 0) {
			if(!isset($langsData[$defLang])) {
				$langsData[$defLang] = ['description' => $defDesc, 'company_name' => $company];
			}
		} else if(!is_null($company)) {
			$langsData[$defLang] = ['description' => $defDesc, 'company_name' => $company];
		}
		
		$userId = 0;
		print_r($data);
		$user = User::createUser($data, [], $langsData);
		if($user) {
			static::$lastUnique = $unique;
			$userId = $user->id;

			if(!empty($profs)) {
				foreach($profs as $prof => $f) {
					$profs[$prof] = static::getProfessionId($prof);
				}
				Profession::saveProfessions(null, $profs, $userId);
			}

			$log['entity_type'] = 1;
			$log['entity_id'] = $userId;
			if(!isset($log['hash'])) {
				$log['hash'] = static::getHash($log);
			}
			static::saveLog($log, $updateResults);
		} else {
			static::setError('Error occurred while saving the user.');
		}
		return $userId;
	}

	public static function moveImages($images)
	{
		$uploads = [];
		$oldPath = static::$uploadTemp;
		$dir = Upload::getUploadsDir();
		$newPath = Upload::getUploadsPath();
		echo 'base: '.count($images);

		foreach($images as $fileNames) {
			if(!is_array($fileNames) || sizeof($fileNames) < 2 || empty($fileNames[0])) continue;
			$fileName = $fileNames[0];
			$name = $dir.str_replace($oldPath, '', $fileName);
			if(rename($fileName, $newPath.'/'.$name)) {
				echo ' moved';
				$uploadItem = new Upload();
				$uploadItem->type = 1;
				$uploadItem->name = $name;
				$uploadItem->save();
				echo ' saved';
				$id = $uploadItem->id;
				$uploads[] = $id;
				//Upload::addWatermark($name, $id);
				static::saveLog(['url' => $fileNames[1], 'entity_type' => 3, 'entity_id' => $id, 'message' => $name], false);
				echo ' logged';
			}
			echo PHP_EOL;
		}
		echo 'end!!! ';
		return $uploads;
	}

	public static function saveNewProperty($data, $log)
	{
		if(is_null($data)) echo 'null';
		$defLang = static::$locale;
		$supportedLangs = static::$supportedLocales;

		$data['langId'] = $defLang;
		$langsData = [];
		//print_r($data);
		//print_r($log);
		//print_r($supportedLangs);
		if(isset($data['descriptions'])) {
			foreach($data['descriptions'] as $lang => $desc) {
				if(isset($supportedLangs[$lang])) {
					$langId = $supportedLangs[$lang]['code'];
					if($langId != $defLang) {
						$langsData[$langId] = ['description' => $desc, 'address' => $data['address'], 'title' => $data['title']];
					}
					if($langId == $defLang) {
						$data['description'] = $desc;
					}
				}
				if(!isset($data['description'])) {
					$data['description'] = $desc;
				}
			}
		}
		//print_r($data);
		//print_r($langsData);
		/*if($data['log']['url'] == 'https://zezoomglobal.com/property/14720553/ua-киев/') {
			print_r($langsData);
			static::doExit();
		}*/
		
		$property = Property::saveItem($data, true, $langsData);

		//print_r($property);
		
		if(isset($property['id'])) {
			$log['entity_type'] = 2;
			$log['entity_id'] = $property['id'];
			if(!isset($log['hash'])) {
				$log['hash'] = static::getHash($log);
			}
			//echo 'id='.$property['id'];
			if(isset($data['user_link'])) {
				$log['message'] = 'UserLink: '.$data['user_link'];
			} else if($data['author'] != 1) {
				$log['message'] = 'Set user: '.$data['author'];
			}
			static::saveLog($log);
		} else {
			//echo 'error=???';
			static::setError('Error occurred while saving the property.'.json_encode($property), true, $log);
		}
	}
    
    public static function updateProperty($id, $data)
    {
        if(is_null($data)) echo 'null';
        $defLang = static::$locale;
        $data['langId'] = $defLang;
        
        $property = Property::updateItem($id, $data, true);
        
        if(!$property) {
            static::setError('Error occurred while updating the property. '.$id.json_encode($property), true, '');
        }
    }

	public static function getProfessionId($name)
	{
		if(isset(static::$professions[$name])) return static::$professions[$name];

		$prof = Profession::select('profession_id')->where([['name', $name], ['lang_id', static::$locale]])->first();
		if(!$prof) {
			$prof = Profession::saveItem(null, ['name' => $name, 'lang_id' => static::$locale], false);
			if(!$prof) {
				static::setError('Error occurred while saving the profession.');
			}
		}

		$id = $prof->profession_id;
		static::$professions[$name] = $id;
		return $id;
	}

	public static function getUrlParam($url, $param) {
		$query = parse_url($url, PHP_URL_QUERY);
		if(!$query) return null;

		$params = explode('&', $query);
		foreach($params as $p){
			$data = explode('=', $p);
			if(sizeof($data) == 2 && $data[0] == $param) {
				return $data[1];
			}
		}
		return null;
	}

	public static function createNewJob()
	{
		$id = static::$parserId;
		echo 'createNewJob'.PHP_EOL;
		dispatch(new ParserJob($id))->onQueue('parser'.$id);
		static::doExit();
	}

	public static function doExit()
	{
		if(!is_null(static::$curl)) {
			static::$curl->__destruct();
		}

		if(!is_null(static::$job)) {
			static::$job->delete();
		}
		exit();
	}
}

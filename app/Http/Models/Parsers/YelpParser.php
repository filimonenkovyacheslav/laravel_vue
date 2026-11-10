<?php

namespace App\Http\Models\Parsers;

use Illuminate\Database\Eloquent\Model;
use Parser;
use ParserLog;
use ProfessionUser;
use User;
use DB;
use \App\Http\Plugins\RollingCurl;

class YelpParser extends BaseParser
{
	public static $parserParams = ['roles', 'locations'];
	public static $userName = '';
	//public static $userEmail = 'yelp@parsed-';
	public static $userEmail = 'info@medicaleer1-';
	public static $parseLimit = 100000;
	public static $hashFields = ['url'];

	public static $location = null;
	public static $blocks = [];
	public static $maxBlocks = 26;

	public static function setLocation($location)
	{
		static::$location = $location;
	}

	public static function run(Parser $parser)
	{
		//return static::runUpdate($parser);

		$url = $parser->url;
		static::setUrl($url);
		$model = $parser->model;
		$params = static::getParams($model);
		$roles = static::getRoles();
		//$parsedResults = static::getParsedResults();
		$search = $url.'/search';
		static::$parsed = 0;
		$curl = new RollingCurl([$model . 'Parser', 'parse']);

		$curl->setUserAgents(static::getUserAgents());
		$curl->setProxies(static::getProxyList(), 'http', 'https://www.yelp.com/', 'title>Restaurants');
		static::setCurl($curl);
		$cntBlocks = 0;
		$cntRoles = sizeof($params['roles']);

		foreach($params['locations'] as $location) {
			static::setLocation($location);
			$city = $location->city;
			$state = $location->state;
			$country = $location->country;

			//if($country != 'United States' || $state < 'Colorado') continue;
			//if($country != 'United States') continue;
			//if($city == 'Ichinomiya') continue;
			//echo $country.', '.$state.', '.$city.PHP_EOL;

			$find_loc = urlencode($city).','.(empty($state) ? '' : urlencode($state).',').urlencode($country);
			$block_loc = $city.','.$state.','.$country;

			$roleNum = 0;
			$blockNum = 0;
			foreach($params['roles'] as $role) {
				$roleNum++;
				$lastRole = ($roleNum >= $cntRoles);

				$roleName = $role->name;

				if($role->key == 'Legal') continue;

				if(isset($roles[$roleName])) {
					$block = $block_loc.'|'.$role->key;

					//if(!isset($parsedResults[$block])) {
					if(!static::getParserResult($block)) {
						$blockNum++;
						$role->id = $roles[$roleName];
						static::$blocks[$blockNum] = ['parsed' => 0, 'role' => $role, 'block' => $block];

						$link = $search.'?find_desc='.urlencode($role->key).'&find_loc='.$find_loc;
						$curl->get($link, null, null, ['type' => 'search', 'block' => $blockNum]);
					}
				}
				if($blockNum >= static::$maxBlocks || ($roleNum >= $cntRoles && $blockNum > 0)) {
					if(!$curl->execute(30)) return true;

					foreach(static::$blocks as $data) {
						static::setParsedResults($data['block'], ['done' => 1, 'parsed' => $data['parsed']]);
					}
					sleep(static::$bigTimeout);
					$curl->__set('requests', []);
					static::$blocks = [];
					$blockNum = 0;
				}
			}
		}

		return true;
	}

	public static function parse($response, $info, $request)
	{
		$proxy = $request->options[CURLOPT_PROXY];
		echo '---------------------'.PHP_EOL;
		echo $request->url.' PROXY:'.$proxy;
		$curl = static::$curl;

		if(static::controlRequestResult($info, $request)) {
			$curl->resetBadProxy($proxy);
		} else {
			$error = $info['http_code'];
			echo '->'.$error;
			$badProxy = in_array($error, [503, 429, 0]);

			if($badProxy || $request->repeat < 2) {
				if($badProxy) {
					$curl->setBadProxy($proxy, $error == 503);
				}
				$request->repeat++;
				$curl->add($request);
				echo '->'.$request->repeat;
			}
			echo PHP_EOL;
			return true;
		}
		echo PHP_EOL;

		echo count($curl->requests).'|'.count($curl->requestMap).'|'.$curl->nProxies.PHP_EOL;

		$reqType = $request->params['type'];
		$reqBlock = $request->params['block'];

		$role = static::$blocks[$reqBlock]['role'];
		$isProfi = ($role->name == 'professional');

		if($reqType == 'search') {
			static::controlStopping();

			$links = static::filterContent($response,'.regular-search-result a.biz-name.js-analytics-click, .pagination-links a.next', 'ul li h3 a, div a.next-link');

			$contentCount = $links->count();
			echo 'search:'.$contentCount;
			if($contentCount == 0 && $request->repeat < 5) {
				$request->repeat++;
				$curl->add($request);
				echo '->'.$request->repeat;
			}
			echo PHP_EOL;

			foreach($links as $node) {
				if(strpos($node->getAttribute('class'), 'next') !== false) {
					$curl->get(static::$url.$node->getAttribute('href'), null, null, ['type' => 'search', 'block' => $reqBlock]);
					echo 'next_break'.PHP_EOL;
				} else {
					$href = $node->getAttribute('href');
					if(strpos($href, '/adredir?') === 0) continue;

					$link = static::$url.$href;

					$log = ['method' => 'get'];
					$parts = explode('?', $link, 2);
					$log['url'] = $parts[0];
					if(sizeof($parts) > 1) {
						$log['params'] = $parts[1];
					}
					//list($log['url'], $log['params']) = explode('?', $link, 2);
					$entityId = static::isParsed($log, 1);
					if($entityId) {
						if($isProfi) {
							static::addProfession($entityId, $role->key, $log);
						}
						static::$blocks[$reqBlock]['parsed']++;
						echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
					} else {
						$curl->get($link, null, null, ['type' => 'user', 'block' => $reqBlock]);
					}
				}
			}
			//dd($curl);
		} elseif($reqType == 'user') {

			//skip users with category Legal
			$professions = static::filterContent($response, 'div.biz-page-header-left span.category-str-list a');
			for($n = 0; $n < $professions->count(); $n++) {
				$node = $professions->eq($n);
				$name = trim($node->text());
				if($name == 'Legal') {
					echo 'LEGAL !!!'.PHP_EOL;
					static::setError('Skip Legal user', false, static::getUrlParts($request->url));
					return true;
				}
			}

			$content = static::filterContent($response, 'h1.biz-page-title, div.mapbox-text, div.mapbox-map a img');

			echo 'user:'.$content->count();

			$contentCount = $content->count();
			if($contentCount < 2 && $request->repeat < 5) {
				$request->repeat++;
				$curl->add($request);
				echo '->'.$request->repeat;
			}
			echo PHP_EOL;

			if($contentCount >= 2) {
				$data = [];
				$profs = [];

				for($n = 0; $n < $contentCount; $n++) {
					$node = $content->eq($n);
					switch($node->nodeName()) {
						case 'h1':
							$name = trim($node->text());
							$pos = strpos($name, ' ');
							if($pos === false) {
								$data['first_name'] = $name;
								$data['last_name'] = ' ';
							} else {
								$data['first_name'] = substr($name, 0, $pos);
								$data['last_name'] = substr($name, $pos + 1);
							}
							break;
						case 'div':
							$data['address'] = static::getNodeText($node, 'address');
							$data['phone'] = static::getNodeText($node, '.biz-phone');
							$data['website'] = static::getNodeText($node, '.biz-website > a');
							break;
						case 'img':
							$src = $node->attr('src');
							$center = static::getUrlParam($src, 'center');
							$parts = explode(',', urldecode($center), 2);
							if(sizeof($parts) == 2) {
								$data['lat'] = $parts[0];
								$data['lng'] = $parts[1];
							} else {
								$marker = urldecode(static::getUrlParam($src, 'markers'));
								$pos = strpos($marker, '.png|');
								if($pos) {
									$parts = explode(',', substr($marker, $pos + 5), 2);
									if(sizeof($parts) == 2) {
										$data['lat'] = $parts[0];
										$data['lng'] = $parts[1];
									}
								}
							}
							break;
						default:
							break;
					}
				}
				if(!isset($data['first_name']) || is_null($data['first_name']) || empty($data['first_name'])) {
					$title = static::filterContent($response, 'div.hidden meta[itemprop="name"]');
					if($title->count() > 0) {
						$name = trim($title->eq(0)->text());
						$pos = strpos($name, ' ');
						if($pos === false) {
							$data['first_name'] = $name;
							$data['last_name'] = ' ';
						} else {
							$data['first_name'] = substr($name, 0, $pos);
							$data['last_name'] = substr($name, $pos + 1);
						}
					}
				}
				if(!isset($data['first_name']) || is_null($data['first_name']) || empty($data['first_name'])) {
					return true;
				}

				if(!isset($data['address']) || is_null($data['address']) || empty($data['address'])) {
					$address = static::filterContent($response, 'div.mapbox-map address');
					if($address->count() > 0) {
						$data['address'] = trim($address->eq(0)->text());
					} else {
						$address = static::filterContent($response, 'div.hidden address[itemprop="address"] span');
						$adrCount = $address->count();
						if($adrCount > 0) {
							$adr = '';
							for($n = 0; $n < $adrCount; $n++) {
								try {
									$adr .= trim($address->eq($n)->text()).', ';
								} catch(Exception $e) {	}
							}
							$data['address'] = substr($adr, 0, -2);
						}
					}
				}
				if(!isset($data['phone']) || is_null($data['phone']) || empty($data['phone'])) {
					$phone = static::filterContent($response, 'div.hidden span[itemprop="telephone"]');
					if($phone->count() > 0) {
						$data['phone'] = trim($phone->eq(0)->text());
					}
				}

				$city = static::filterContent($response, 'div.hidden span[itemprop="addressLocality"]');
				if($city->count() > 0) {
					$data['city'] = trim($city->eq(0)->text());
				}

				$data['role_id'] = $role->id;
				$data['role_name'] = $role->name;

				$location = static::$location;
				if(!isset($data['city']) || is_null($data['city']) || empty($data['city'])) {
					$data['city'] = $location->city;
				}
				if($location->country == 'Japan' && empty(trim($data['last_name']))) {
					$data['last_name'] = 'Japan';
				}

				//$data['city'] = $location->city;
				$data['country'] = $location->country;

				if($isProfi) {
					$profs[$role->key] = 0;
				}
				$log = ['method' => 'get'];
				//list($log['url'], $log['params']) = explode('?', $request->url, 2);
				$log = static::getUrlParts($request->url, $log);

				static::saveNewUser($data, $log, $profs);
				static::$parsed++;
				static::$blocks[$reqBlock]['parsed']++;
				echo ' parsed='.(static::$blocks[$reqBlock]['parsed']).'|'.static::$parsed.PHP_EOL;
				if(static::$parsed >= static::$parseLimit) return false;
			}
		}

		return true;
	}

	public static function addProfession($userId, $prof, $log)
	{
		$user = User::find($userId);
		if(!$user || $user->role_id != 8) return false;

		$id = static::getProfessionId($prof);
		$exists = ProfessionUser::select('id')->where([['user_id', $userId], ['profession_id', $id]])->first();
		if(!$exists) {
			$item = new ProfessionUser;
			$item->fill(['user_id' => $userId, 'profession_id' => $id])->save();

			$log['parser_id'] = static::$parserId;
			$log['entity_type'] = 1;
			$log['entity_id'] = $userId;
			$log['message'] = 'add profession';
			unset($log['hash']);
			ParserLog::saveData($log);

			echo 'addProfession ';
		}
	}

	public static function runUpdate($parser)
	{
		$curl = new RollingCurl([$parser->model . 'Parser', 'parseUpdate']);

		$users = User::select(DB::raw('users.id, p.url'))
			->join('parser_log as p', 'p.entity_id', '=', 'users.id')
			->where([['users.name', ''], ['p.parser_id', 1], ['p.entity_type', 1]])
			->whereNull('users.address')
			->limit(40000)
			->get();

		$users = $users ? $users->toArray() : [];

		$cntUsers = sizeof($users);
		echo 'count='.$cntUsers.PHP_EOL;
		if($cntUsers == 0) return true;

		$curl->setUserAgents(static::getUserAgents());
		$curl->setProxies(static::getProxyList(), 'http', 'https://www.yelp.com/', 'title>Restaurants');
		static::setCurl($curl);
		$cnt = 0;
		$num = 0;
		$limit = 1000;
		static::$parsed = 0;

		foreach($users as $user) {
			$cnt++;
			$num++;
			$curl->get($user['url'], null, null, ['id' => $user['id']]);
			if($cnt >= $limit || $num >= $cntUsers) {
				if(!$curl->execute(30)) return true;

				//return true;
				sleep(static::$bigTimeout);
				$curl->__set('requests', []);
				$cnt = 0;
			}
		}
		return true;
	}

	public static function parseUpdate($response, $info, $request)
	{
		$proxy = $request->options[CURLOPT_PROXY];
		echo '---------------------'.PHP_EOL;
		echo $request->url.' PROXY:'.$proxy;
		$curl = static::$curl;

		if(static::controlRequestResult($info, $request)) {
			$curl->resetBadProxy($proxy);
		} else {
			$error = $info['http_code'];
			echo '->'.$error;
			$badProxy = in_array($error, [503, 429, 0]);

			if($badProxy || $request->repeat < 2) {
				if($badProxy) {
					$curl->setBadProxy($proxy, $error == 503);
				}
				$request->repeat++;
				$curl->add($request);
				echo '->'.$request->repeat;
			}
			echo PHP_EOL;
			return true;
		}
		echo PHP_EOL;

		echo count($curl->requests).'|'.count($curl->requestMap).'|'.$curl->nProxies.PHP_EOL;

		static::controlStopping();

		$content = static::filterContent($response, 'div.mapbox-text');
		$contentCount = $content->count();
		echo 'user:'.$contentCount.'->';
		$data = [];
		if($contentCount > 0) {
			$node = $content->eq(0);
			$data['address'] = static::getNodeText($node, 'address');
			$data['phone'] = static::getNodeText($node, '.biz-phone');
			$data['website'] = static::getNodeText($node, '.biz-website > a');
			echo 'found1 ';
		}
		if(!isset($data['address']) || is_null($data['address']) || empty($data['address'])) {
			$address = static::filterContent($response, 'div.mapbox-map address');
			if($address->count() > 0) {
				$data['address'] = trim($address->eq(0)->text());
				echo 'found2 ';
			} else {
				$address = static::filterContent($response, 'div.hidden address[itemprop="address"] span');
				$adrCount = $address->count();
				if($adrCount > 0) {
					echo 'found3('.$adrCount.')';
					$adr = '';
					for($n = 0; $n < $adrCount; $n++) {
						try {
							$adr .= trim($address->eq($n)->text()).', ';
						} catch(Exception $e) {	}
					}
					$data['address'] = substr($adr, 0, -2);
				}
			}
		}
		if(!isset($data['phone']) || is_null($data['phone']) || empty($data['phone'])) {
			$phone = static::filterContent($response, 'div.hidden span[itemprop="telephone"]');
			if($phone->count() > 0) {
				$data['phone'] = trim($phone->eq(0)->text());
				echo 'found4 ';
			}
		}

		$city = static::filterContent($response, 'div.hidden span[itemprop="addressLocality"]');
		if($city->count() > 0) {
			$location = trim($city->eq(0)->text());
			if(isset($location) && !is_null($location) && !empty($location)) {
				$data['city'] = $location;
				echo 'found5 ';
			}
		}

		if(isset($data['address']) || isset($data['phone'])) {
			$user = User::where('id', $request->params['id'])->first();
			if($user) {
				$user->fill($data)->save();
				static::$parsed++;
				echo 'SAVE id='.$request->params['id'].' | '.static::$parsed;
			}
		}
		echo PHP_EOL;
		return true;
	}

}

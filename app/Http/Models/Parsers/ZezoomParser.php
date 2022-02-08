<?php

namespace App\Http\Models\Parsers;

use Illuminate\Database\Eloquent\Model;
use Parser;
use ParserLog;
use ProfessionUser;
use User;
use Country;
use Property;
use Setting;
use DB;
use Upload;
use UploadProperty;
use \App\Http\Plugins\RollingCurl;
use Intervention\Image\Facades\Image;

class ZezoomParser extends BaseParser
{
	public static $parserParams = ['locations'];
	public static $userName = '';
	public static $userEmail = 'info@medicaleer2-';
	public static $imageName = 'medicaleer2_';
	public static $parseLimit = 0;
	public static $parseBlockLimit = 1000;
	public static $parsedBlocks = 0;
	public static $parsedUsers = 0;
	public static $hashFields = ['url'];
	public static $searchUrl = '';

	public static $location = null;
	public static $blocks = [];
	public static $maxBlocks = 1;

	public static $propStatuses = ['For rent' => 1, 'For sale' => 2];
	public static $propTypes = ['House' => 2, 'Apartment' => 1, 'Lot/Land' => 11, 'Villa' => 2, 'Chalet' => 2, 'Castle' => 2, 'Business office' => [6, 1],
		'Residential building' => 2, 'Bungalow' => 2, 'Garage parking' => [6, 10], 'Other' => 15,
		'Farm' => [6, 9], 'Commercial' => 6, 'Industrial building' => [6, 4]];

	public static $properties = [];
	public static $propertyIdent = 0;
	public static $users = [];
	public static $userIdent = 0;
	public static $imageIdent = 0;
	public static $imagesErrors = 0;
	public static $imagesErrorLimit = 500;
	public static $leerImages = [
		'52ce81df817053b98c62379d93a3e6bb', '6b243f62d70373eeade2f2cb5866c678',
		'95521009dfa769f353ac6d07e583dee6', 'bc09560da3ad29f7b3d9b7d04169b7c0',
		'0bbd03e6ab8dae1f72593108207f2aab', '6b1b95297363168987eab153d8b0ef6a',
		'0e74e5a5f11a66beaf2bb6eec5cd48df', 'e3ac255e38abe0089afa0fbcafe3a5b3',
		'9df30da6015cd9e4351122619e6e9464', '86c5f0dbde6da36fb496592f767972c2'];
	public static $imageTypes = [1 => '.gif', 2 => '.jpg', 3 => '.png'];

	public static $geoKeys = ['app_id=8fy5YYQODu285Qdh4bDu&app_code=smuGiN8rVV5n-Ob2M3ZheQ', 'app_id=Y6ubL4jglozAlggMCAQm&app_code=2s-0iPRrGsTAPB-z6tYdtA'];
	public static $geoKeyNum = 0;
	public static $geoKeyMax = 1;

	public static $iso2 = '';
	public static $errorContries = ['Hongkong' => 'Hong Kong', 'Republic of Ireland' => 'Ireland',
		 'Korea' => 'Korea, Republic of', 'Macedonia' => 'Macedonia, The Former Yugoslav Republic of', 'Russia' => 'Russian Federation'];

	public static function run(Parser $parser)
	{
		static::deleteAllImagesForDeletedProperties(); return true;

		$url = $parser->url;
		static::setUrl($url);
		$model = $parser->model;
		$params = static::getParams($model);

		static::prepareTempDir();
		static::$imageName .= date('YmdHis').'_';

		$curl = new RollingCurl([$model . 'Parser', 'parse']);
		$curl->setCookie('currency=USD; domain=.zezoomglobal.com');
		$curl->setUserAgents(static::getUserAgents());

		if(isset($params['proxies']) && is_array($params['proxies']) && sizeof($params['proxies']) > 300) {
			$curl->setAliveProxies($params['proxies']);
		} else {
			$curl->setProxies(static::getProxyList(), 'http', 'https://zezoomglobal.com/', 'ZEZOOM Global', 700);
		}
		static::setCurl($curl);
		$countries = Country::getCoyntriesForSelect();

		//static::runUpdate(); return true;
		if(!static::updateAuthors($countries)) return true;
		//return true;

		//$parsedResults = static::getParsedResults(null);

		static::$parsed = 0;
		$cntBlocks = 0;
		$defResult = ['done' => 0, 'url' => null, 'page' => 0];

		foreach($params['locations'] as $location) {
			$city = $location->city;
			$state = $location->state;
			$country = trim($location->country);

			if(isset(static::$errorContries[$country])) {
				$country = static::$errorContries[$country];
			}

			//if($city != 'Toulouse') continue;

			$find_loc = urlencode($city).','.(empty($state) ? '' : urlencode($state).',').urlencode($country);
			$block_loc = $city.','.$state.','.$country;

			$blockNum = 0;

			$block = $block_loc;

			//echo $find_loc.PHP_EOL;
			$parsedResult = static::getParsedResults($block);
			echo $block.PHP_EOL;
			print_r($parsedResult);
			if(!isset($parsedResult['done'])) $parsedResult = $defResult;
			//$parsedResult = isset($parsedResults[$block]) ? $parsedResults[$block] : $defResult;
			if($parsedResult['done'] == 0) {
				print_r($parsedResult);
				$countryId = array_search($country, $countries);
				if($countryId === false) {
					static::setError('Not found country: '.$country);
				}
				static::$iso2 = Country::where('id', $countryId)->value('iso2');
				$blockNum++;
				static::$blocks[$blockNum] = ['parsed' => 0, 'block' => $block, 'city' => $city, 'state' => $state, 'country' => $countryId, 'countryName' => $country, 'page' => 0, 'new' => true];

				//$link = $url.'/'.$propType.'/';
				//$curl->get($link, null, null, ['type' => 'search', 'block' => $blockNum]);
				if(is_null($parsedResult['url'])) {
					$link = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$find_loc.'&key=AIzaSyArIgPnN3sPzz5EsemlLkiqM1hPhqJDcLI';
					$curl->get($link, null, null, ['type' => 'geosearch', 'block' => $blockNum]);
				} else {
					$link = $parsedResult['url'];
					$page = is_null($parsedResult['page']) ? 0 : $parsedResult['page'];
					if($page > 0) {
						static::$blocks[$blockNum]['page'] = $page;
						static::$blocks[$blockNum]['new'] = false;
					}
					$page++;
					$curl->get($link.($page > 1 ? $page.'-page/' : '').'?agc=on', null, null, ['type' => 'list', 'page' => $page, 'url' => $link, 'block' => $blockNum]);
					//$curl->get($link.'?agc=on', null, null, ['type' => 'list', 'page' => 1, 'url' => $link, 'block' => $blockNum]);
				}
				echo '!!!!!'.$link.PHP_EOL;
			}
			if($blockNum >= static::$maxBlocks) {
				static::$properties = [];
				static::$propertyIdent = 0;
				static::$users = [];
				static::$userIdent = 0;
				echo 'execute'.PHP_EOL;
				gc_collect_cycles();
				if(!$curl->execute(100)) return true;

				foreach(static::$blocks as $reqBlock => $data) {
					if(sizeof(static::$properties) > 0) {
						echo 'Images Load Error->'.sizeof(static::$properties).PHP_EOL;
						foreach(static::$properties as $ident => $property) {
							if(sizeof($property['images']) > $property['cnt_images']) {
								static::saveProperty($property, $ident, $reqBlock);
								static::$imagesErrors++;
							} else {
								$log = $property['log'];
								unset($log['hash']);
								static::setError('Images Load Error: '.$property['cnt_images'], false, $log);
								static::$imagesErrors++;
							}
						}
					}
					if(!empty(static::$imagesErrorLimit) && static::$imagesErrors >= static::$imagesErrorLimit) {
						static::setError('Too many image upload errors: '.static::$imagesErrors);
					}

					static::setParsedResults($data['block'], ['done' => ($data['new'] ? 1 : 0), 'page' => 0, 'parsed' => $data['parsed']]);
					static::$parsedBlocks++;
				}
				echo ' parsedBlocks='.static::$parsedBlocks.PHP_EOL;
				if(!empty(static::$parseBlockLimit) && static::$parsedBlocks >= static::$parseBlockLimit) return true;

				static::controlStopping();
				if(static::$parsed > 1500) {
					Setting::setValue('parsers', 'zezoom_proxies', json_encode($curl->getAliveProxies()));
					static::createNewJob();
				}
				//sleep(static::$bigTimeout);
				$curl->__set('requests', []);
				static::$blocks = [];
				$blockNum = 0;
				if(!static::updateAuthors($countries)) return true;
				$curl->__set('requests', []);
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

		//$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		//$header = substr($response, 0, $header_size);
		//$body = substr($response, $header_size);
		static::controlStopping();

		switch($reqType) {
			case 'geosearch':
				$obj = @json_decode($response);
				if(!isset($obj->results[0])) {
					if($request->repeat < 3) {
						$request->repeat++;
						$curl->add($request);
						echo '->'.$request->repeat;
					} else {
						static::setError('Geocode not found', false, static::getUrlParts($request->url));
					}
				}

				if(isset($obj->results[0]->geometry) && isset($obj->results[0]->geometry->bounds)) {
					$bounds = $obj->results[0]->geometry->bounds;
					$link = 'https://zezoomglobal.com/for-rent/'.$bounds->southwest->lat.','.$bounds->southwest->lng.','.$bounds->northeast->lat.','.$bounds->northeast->lng.'-bounds/10-zm/96-entry/1,2-cat/';
					static::setParsedResults(static::$blocks[$reqBlock]['block'], ['url' => $link, 'done' => 0, 'parsed' => 0, 'page' => 0]);
					$curl->get($link.'?agc=on', null, null, ['type' => 'list', 'page' => 1, 'url' => $link, 'block' => $reqBlock]);
				}
				break;

			case 'list':
				$obj = @json_decode($response);
				if(!isset($obj->params) && $request->repeat < 5) {
					$request->repeat++;
					$curl->add($request);
					echo '->'.$request->repeat.PHP_EOL;
					return true;
				}

				//echo $obj->params->count.'-> ';
				$cntProps = 0;
				if(isset($obj->template)) {
					$properties = static::filterContent($obj->template, 'div.property-row-wrapper');
					$cntProps = $properties->count();
				}

				echo 'cntProps:'.$cntProps.PHP_EOL;
				if($cntProps > 0) {
					$cntNew = 0;
					$page = $request->params['page'];
					for($n = 0; $n < $cntProps; $n++) {
						$node = $properties->eq($n);
						$href = $node->filter('div.property-list-image a');
						if($href->count() != 1) continue;

						$link = static::$url.$href->attr('href');
						if(!$link || strlen($link) < 10) continue;
						if($link == 'https://zezoomglobal.com/property/21752496/us/') continue;
						//if($link == 'https://zezoomglobal.com/property/21601901/fr/') continue;
						//if($link == 'https://zezoomglobal.com/property/21965692/fr/') continue;

						$log = ['method' => 'get', 'url' => $link];
						if(static::isParsed($log, 2)) {
							static::$blocks[$reqBlock]['parsed']++;
							echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
						} else {
							$cntNew++;
							$attrs = $node->filter('div.property-list-description div.col-xs-9 div.attr-value');
							$curl->get($link, null, null, ['type' => 'property', 'prop_type' => ($attrs->count() > 0 ? trim($attrs->eq(0)->text()) : ''), 'log' => $log, 'block' => $reqBlock]);
						}
					}
					if($cntNew <= 5 && (static::$blocks[$reqBlock]['page'] + 1) == $page) {
						static::setParsedResults(static::$blocks[$reqBlock]['block'], ['page' => $page]);
						static::$blocks[$reqBlock]['page'] = $page;
					}

					$page++;
					$link = $request->params['url'];
					if($cntProps >= 96) {
						echo 'next page '.$page.PHP_EOL;
						$curl->get($link.$page.'-page/?agc=on', null, null, ['type' => 'list', 'page' => $page, 'url' => $link, 'block' => $reqBlock]);
					}
				}
				break;

			case 'property':
				$content = static::filterContent($response, 'div.property-container');
				if($content->count() == 0 && $request->repeat < 5) {
					$request->repeat++;
					$curl->add($request);
					echo '->'.$request->repeat.PHP_EOL;
					return true;
				}

				//if(sizeof(static::$properties) > 10) return true;

				$data = [];
				$node = $content->eq(0);

				$data['log'] = $request->params['log'];
				$title = $node->filter('h2.header-split span.full-length');
				$data['title'] = $title->count() > 0 ? $title->attr('data-title') : '';
				if(empty($data['title'])) return true;

				$price = static::getNodeText($node, 'span.property-price', false);
				if(!is_null($price)) {
					$price = str_replace('USD', '', str_replace(' ', '', $price));
				}
				$data['price'] = ($price && is_numeric($price)) ? $price : 1;

				$flags = $node->filter('div.flag-trigger a img');
				$flagsCnt = $flags->count();
				if($flagsCnt > 0) {
					$desc = $node->filter('div.flag-content div.holder p');
					$descCnt = $desc->count();
					$n = 0;
					$data['descriptions'] = [];
					foreach($flags as $n => $flag) {
						$lang = str_replace('.png', '', str_replace('media/flag/', '', $flag->getAttribute('src')));
						$data['descriptions'][$lang] = ($descCnt > $n ? $desc->eq($n)->html() : '');
						$n++;
					}
				}
				$propType = $request->params['prop_type'];
				if(isset(static::$propTypes[$propType])) {
					$typeSubType = static::$propTypes[$propType];
					if(is_array($typeSubType)) {
						$data['property_type'] = $typeSubType[0];
						$data['property_subtype'] = $typeSubType[1];
					} else {
						$data['property_type'] = $typeSubType;
					}
				} else {
					static::setError('New property type: '.$propType, true, static::getUrlParts($request->url));
				}

				$status = static::getNodeText($node,'span.property-category');
				$data['property_status'] = isset(static::$propStatuses[$status]) ? static::$propStatuses[$status] : 2;

				$data['address'] = static::getNodeText($node,'div.property-address', false);
				$data['map_address'] = $data['address'];
				$block = static::$blocks[$reqBlock];
				$data['city'] = $block['city'];
				$data['state'] = $block['state'];
				$data['country'] = $block['country'];
				$lat = static::filterContent($response, 'meta[property="place:location:latitude"]');
				$lng = static::filterContent($response, 'meta[property="place:location:longitude"]');
				if($lat->count() > 0) {
					$data['lat'] = $lat->eq(0)->attr('content');
				}
				if($lng->count() > 0) {
					$data['lng'] = $lng->eq(0)->attr('content');
				}

				if($data['title'] == static::$iso2) {
					$data['title'] .= ', '.$data['city'];
				}

				$features = $node->filter('table.property-feature tbody tr');
				$featuresCnt = $features->count();
				if($featuresCnt > 0) {
					for($n = 0; $n < $featuresCnt; $n++) {
						$feature = $features->eq($n)->filter('td');
						if($feature->count() == 2) {
							$name = trim($feature->eq(0)->text());
							$value = trim($feature->eq(1)->text());
							switch($name) {
								case 'Bathrooms':
									$data['bathrooms'] = $value;
									break;
								case 'Rooms':
									$data['bedrooms'] = $value;
									break;
								case 'Beds Sleeping':
									if(!isset($data['bedrooms'])) {
										$data['bedrooms'] = $value;
									}
									break;
								case 'LivingRoom Number':
									if(!isset($data['bedrooms'])) {
										$data['bedrooms'] = $value;
									}
									break;
								case 'Surface of living area':
									$data['property_area'] = floatval(str_replace(' ', '', $value));
									$data['property_area_measure'] = 1;
									break;
								case 'Lot size':
									$data['land_area'] = floatval(str_replace(' ', '', $value));
									$data['land_area_measure'] = 1;
									break;
								case 'AirConditioning':
									$data['features'][] = 1;
									break;
								case 'Terraces':
									if(floatval(str_replace(' ', '', $value)) > 0) {
										$data['features'][] = 55;
									}
									break;
								case 'Terraces Type':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 55;
									}
									break;
								case 'Terraces Surface':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 55;
									}
									break;
								case 'Lifts':
									if(floatval(str_replace(' ', '', $value)) > 0) {
										$data['features'][] = 31;
									}
									break;
								case 'Decoration Fireplace':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 18;
									}
									break;
								case 'LaundryRoom':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 29;
									}
									break;
								case 'Garage':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 20;
									}
									break;
								case 'Parking Place(s)':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 34;
									}
									break;
								case 'Parking Private':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 34;
									}
									break;
								case 'Parking Type':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 34;
									}
									break;
								case 'Pool':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 53;
									}
									break;
								case 'Garden Barbecue':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 9;
									}
									break;
								case 'Security Guard':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 41;
									}
									break;
								case 'Security Caretaker':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 41;
									}
									break;
								case 'SwimmingPool':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 53;
									}
									break;
								case 'GameRoom':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 19;
									}
									break;
								case 'Equipments Hammam':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 24;
									}
									break;
								case 'Security DigiCode':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 43;
									}
									break;
								case 'Security AutomaticPortal':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 43;
									}
									break;
								case 'Security Secure':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 41;
									}
									break;
								case 'Security EntryPhone':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 43;
									}
									break;
								case 'Security VideoPhone':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 43;
									}
									break;
								case 'Outbuilding Type':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 32;
									}
									break;
								case 'Outbuilding Surface':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 32;
									}
									break;
								case 'Security Alarm':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 2;
									}
									break;
								case 'Security ArmouredDoor':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 42;
									}
									break;
								case 'Parking Size':
									if(floatval(str_replace(' ', '', $value)) > 0) {
										$data['features'][] = 34;
									}
									break;
								case 'Garden Type':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 21;
									}
									break;
								case 'Garden Surface':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 21;
									}
									break;
								case 'Sport Tennis':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 54;
									}
									break;
								case 'Windows Shutter':
									if(strlen($value) > 0 && $value != 'no') {
										$data['features'][] = 46;
									}
									break;
								case 'Windows Glazing':
									if($value == 'DoubleGlazing') {
										$data['features'][] = 15;
									} else {
										static::setError('New feature: '.$name.' => '.$value, false, static::getUrlParts($request->url));
									}
									break;
								case 'Heating Type':
									if($value == 'CentralHeating') {
										$data['features'][] = 11;
									} else if ($value == 'UnderfloorHeating') {
										$data['features'][] = 56;
									}
									break;
								case 'Leisure Room':
									if($value == 'Library') {
										$data['features'][] = 30;
									} else {
										static::setError('New feature: '.$name.' => '.$value, false, static::getUrlParts($request->url));
									}
									break;
								case 'Built in':
									$data['year_built'] = $value;
									break;
								case 'PropertyStatus':
								case 'Floor':
								case 'AgentID':
								case 'Attic':
								case 'Availability Rent':
								case 'DressingRoom':
								case 'Equipments Phone':
								case 'Floor Type':
								case 'Facilities Type':
								case 'Insulation':
								case 'Internet':
								case 'Internet Type':
								case 'Heating Collective':
								case 'HandicappedAccessible':
								case 'Kitchen Equipment':
								case 'Kitchen Equipped':
								case 'Kitchen Number':
								case 'Kitchen Surface':
								case 'Kitchen Style':
								case 'LivingRoom Surface':
								case 'Locality CityPart':
								case 'Location':
								case 'Mezzanine':
								case 'Mezzanine Surface':
								case 'Courtyard':
								case 'Courtyard Surface':
								case 'Price AgentFeesIncluded':
								case 'Price PersonalPropertyTaxes':
								case 'Parking Surface':
								case 'Proximity Destination':
								case 'Proximity Distance':
								case 'Toilets Separated':
								case 'Renovation':
								case 'Availability Day':
								case 'Availability Month':
								case 'Availability Immediate':
								case 'DiningRoom Number':
								case 'DiningRoom Surface':
								case 'Style Roof':
								case 'Style':
								case 'Style Condition':
								case 'Composition Levels':
								case 'Equipments TV':
								case 'Exposure':
								case 'View':
								case 'Pets':
									break;
								default:
									static::setError('New feature: '.$name.' => '.$value, false, static::getUrlParts($request->url));
									return true;
									break;
							}
						}
					}
				}
				$data['author'] = 1;
				$data['status'] = 1;
				$data['currency_code'] = 840;
				$data['cnt_images'] = 0;

				$user = $node->filter('#agents-listing a');
				if($user->count() > 0) {
					$href = $user->eq(0)->attr('href');
					if($href && strlen($href) > 5) {
						$link = static::$url.$href;
						$log = ['method' => 'get', 'url' => $link];

						$userId = static::isParsed($log, 1);

						if($userId) {
							$data['author'] = $userId;
							echo 'USER isParsed->'.$userId.PHP_EOL;
						} else {
							$data['user_link'] = $link;
							$curl->get($link, null, null, ['type' => 'user', 'log' => $log, 'city' => $block['city'], 'state' => $block['state'], 'country' => $block['countryName'], 'block' => 0]);
						}
					}
				}

				$images = $node->filter('#carousel a img');
				$imagesCnt = $images->count();
				$data['images'] = [];
				if($imagesCnt > 0) {
					static::$propertyIdent++;
					for($n = 0; $n < $imagesCnt; $n++) {
						static::$imageIdent++;
						$link = $images->eq($n)->attr('src');
						if(!$link || strlen($link) < 10) continue;

						$end = strpos($link, '&width=');
						if($end > 0) {
							$link = substr($link, 0, $end);
						}
						$link = str_replace(' ', '%20', $link);
						if(substr($link, 0, 4) != 'http') {
							$link = static::$url.(substr($link, 0, 1) == '/' ? '' : '/').$link;
						}
						$data['images'][static::$imageIdent] = [];
						$curl->get($link, null, null, ['type' => 'image', 'prop_ident' => static::$propertyIdent, 'image_ident' => static::$imageIdent, 'block' => $reqBlock]);
					}
					$data['cnt_images'] = $imagesCnt;
					$data['leer_images'] = 0;
				}
				echo 'images='.$data['cnt_images'].PHP_EOL;

				if(empty($data['cnt_images'])) {
					if(!static::saveProperty($data, 0, $reqBlock)) return false;
				} else {
					static::$properties[static::$propertyIdent] = $data;
				}
			break;
			case 'image':
				$imageIdent = $request->params['image_ident'];
				$propertyIdent = $request->params['prop_ident'];
				$fileName = static::$uploadTemp.'/'.static::$imageName.$imageIdent.'.jpg';

				$size = static::saveImage($fileName, $response, $info);
				if($size === 0) {
					$fileName = '';
					static::$properties[$propertyIdent]['leer_images']++;
				}

				if(!empty($fileName) && ($size === false)) {
					//$errorType = ($info['content_type'] != 'image/jpeg');
					if($request->repeat < 5) { //} && !$errorType) {
						$request->repeat++;
						$curl->add($request);
						echo '->'.$request->repeat.PHP_EOL;
						echo $fileName.' $size='.$size;
						print_r($info);
						echo static::$properties[$propertyIdent]['log']['url'].PHP_EOL;
						return true;
					} /*else {
						echo ($errorType ? 'ErrorTYPE: '.$info['content_type'] : 'ErrorLOAD').PHP_EOL;
						static::setError(($errorType ? 'Not JPG: '.$info['content_type'] : 'Error img load for:').' '.static::$properties[$propertyIdent]['log']['url'], false, static::getUrlParts($request->url));
					}*/
				} else {
					static::$properties[$propertyIdent]['images'][$imageIdent] = [$fileName, $request->url];
				}

				static::$properties[$propertyIdent]['cnt_images']--;

				if(empty(static::$properties[$propertyIdent]['cnt_images'])) {
					if(!static::saveProperty(static::$properties[$propertyIdent], $propertyIdent, $reqBlock)) return false;
				}

				break;
			case 'user':
				$content = static::filterContent($response, 'div.profile-module');
				if($content->count() == 0 && $request->repeat < 5) {
					$request->repeat++;
					$curl->add($request);
					echo '->'.$request->repeat.PHP_EOL;
					return true;
				}
				$data = [];
				$node = $content->eq(0);

				$data['log'] = $request->params['log'];
				if(isset($request->params['property_id'])) {
					$data['property_id'] = $request->params['property_id'];
					if(isset($request->params['log_id'])) {
						$data['log_id'] = $request->params['log_id'];
					}
				}
				$data['role_id'] = 2;

				$name = static::getNodeText($node, 'div.profile-name h1', false);
				if(is_null($name)) return true;
				$pos = strpos($name, ' ');
				if($pos === false) {
					$data['first_name'] = $name;
					$data['last_name'] = ' ';
				} else {
					$data['first_name'] = substr($name, 0, $pos);
					$data['last_name'] = substr($name, $pos + 1);
				}
				$company = static::getNodeText($node, 'div.profile-name span', false);
				if(!is_null($company)) {
					$data['company_name'] = $company;
					if(strlen($data['first_name']) <= 3 && $data['last_name'] == ' ') {
						$data['last_name'] = $company;
					}
				}

				$data['city'] = $request->params['city'];
				$data['state'] = $request->params['state'];
				$data['country'] = $request->params['country'];

				if($data['first_name'] == '-' && ($data['last_name'] == ' ' || $data['last_name'] == '-')) {
					$data['last_name'] = $data['country'];
				} else if($data['first_name'] == '---' && $data['last_name'] == '---') {
					$data['last_name'] = $data['country'];
				}

				$social = $node->filter('div.profile-name div.profile-social-media a');
				$socialCnt = $social->count();
				if($socialCnt > 0) {
					for($n = 0; $n < $socialCnt; $n++) {
						$href = $social->eq($n);
						$value = $href->attr('href');
						if(strlen($value) < 10) continue;

						$field = $href->attr('title');
						switch($field) {
							case 'Facebook':
								$data['facebook'] = $value;
								break;
							case 'Linkedin':
								$data['linkedin'] = $value;
								break;
							case 'Twitter':
								$data['twitter'] = $value;
								break;
							default:
								static::setError('New user social: '.$field.' => '.$value, false, static::getUrlParts($request->url));
								return true;
								break;
						}
					}
				}

				$flags = $node->filter('div.profile-description div.flag-trigger a img');
				$flagsCnt = $flags->count();
				if($flagsCnt > 0) {
					$desc = $node->filter('div.profile-description div.flag-content div.holder p');
					$descCnt = $desc->count();
					$n = 0;
					$data['descriptions'] = [];
					foreach($flags as $n => $flag) {
						$lang = str_replace('.png', '', str_replace('media/flag/', '', $flag->getAttribute('src')));
						$data['descriptions'][$lang] = ($descCnt > $n ? $desc->eq($n)->html() : '');
						$n++;
					}
				}

				static::$userIdent++;

				$profile = $node->filter('div.profile-data');
				if($profile->count() > 0) {
					$profNode = $profile->eq(0);
					$fields = $profNode->filter('span');
					$values = $profNode->filter('div.value');
					$fieldsCnt = $fields->count();
					if($fieldsCnt > 0 && $values->count() >= $fieldsCnt) {
						for($n = 0; $n < $fieldsCnt; $n++) {
							$field = trim($fields->eq($n)->text());
							$value = trim($values->eq($n)->text());
							if($value == 'n/a' || strlen($value) == 0) continue;

							switch($field) {
								case 'Address':
									$data['address'] = $value;
									break;
								case 'Phone':
									$data['phone'] = $value;
									break;
								case 'Fax':
									$data['fax_number'] = $value;
									break;
								case 'Website':
									$data['website'] = $value;
									break;
								default:
									static::setError('New user field: '.$field.' => '.$value, false, static::getUrlParts($request->url));
									return true;
									break;
							}
						}
					}
					$image = $profNode->filter('div.profile-photo img');
					if($image->count() > 0) {
						$link = trim($image->eq(0)->attr('src'));
						if($link && strlen($link) > 10 && $link != 'media/no-photo-user.png') {

							$end = strpos($link, '&width=');
							if($end > 0) {
								$link = substr($link, 0, $end);
							}
							$link = str_replace(' ', '%20', $link);
							if(substr($link, 0, 4) != 'http') {
								$link = static::$url.(substr($link, 0, 1) == '/' ? '' : '/').$link;
							}
							static::$imageIdent++;
							//echo 'image='.$link.PHP_EOL;
							$curl->get($link, null, null, ['type' => 'user_image', 'user_ident' => static::$userIdent, 'image_ident' => static::$imageIdent, 'block' => 0]);
							$data['image'] = '';
						}
					}
				}


				if(!isset($data['address']) || empty($data['address'])) {
					$data['address'] = $data['city'].', '.((isset($data['state']) && !is_null($data['state']) && !empty($data['state'])) ? $data['state'].', ' : '').$data['country'];
				}
				$data['map_address'] = $data['address'];

				static::$geoKeyNum++;
				if(static::$geoKeyNum > static::$geoKeyMax) {
					static::$geoKeyNum = 0;
				}

				//$link = 'https://geocoder.api.here.com/6.2/geocode.json?app_id=Y6ubL4jglozAlggMCAQm&app_code=2s-0iPRrGsTAPB-z6tYdtA&language=en&searchtext='.urlencode($data['address']);
				$link = 'https://geocoder.api.here.com/6.2/geocode.json?'.static::$geoKeys[static::$geoKeyNum].'&language=en&searchtext='.urlencode($data['address']);
				$curl->get($link, null, null, ['type' => 'user_geosearch', 'user_ident' => static::$userIdent, 'block' => 0]);
				$data['geo'] = '';

				if(isset($data['image']) || isset($data['geo'])) {
					static::$users[static::$userIdent] = $data;
				} else {
					if(!static::saveUser($data, 0)) return false;
				}
				//print_r($data);
				//return false;
				break;
			case 'user_image':
				$imageIdent = $request->params['image_ident'];
				$userIdent = $request->params['user_ident'];
				$fileName = static::$uploadTemp.'/'.static::$imageName.$imageIdent.'.jpg';

				$size = static::saveImage($fileName, $response, $info);
				if($size === 0) {
					$fileName = '';
				}
				//echo '$userIdent'.$userIdent;
				//print_r(static::$users[$userIdent]['image']);
				if(!empty($fileName) && ($size === false)) {
					if($request->repeat < 5) {
						$request->repeat++;
						$curl->add($request);
						echo '->'.$request->repeat.PHP_EOL;
						//echo $fileName.' $size='.$size;
						return true;
					}
				} else {
					static::$users[$userIdent]['image'] = [$fileName, $request->url];
				}
				//print_r(static::$users[$userIdent]['image']);

				if(!static::saveUser(static::$users[$userIdent], $userIdent)) return false;

				break;
			case 'user_geosearch':
				$obj = @json_decode($response);
				if(!isset($obj->Response)) {
					if($request->repeat < 3) {
						$request->repeat++;
						$curl->add($request);
						echo '->'.$request->repeat;
					} else {
						static::setError('User Geocode not found', false, static::getUrlParts($request->url));
					}
				}

				$userIdent = $request->params['user_ident'];
				if(isset($obj->Response) && isset($obj->Response->View) && isset($obj->Response->View[0]) && isset($obj->Response->View[0]->Result[0]) && isset($obj->Response->View[0]->Result[0]->Location)) {

					$location = $obj->Response->View[0]->Result[0]->Location;
					if(isset($location->Address)) {
						if(isset($location->Address->City)) {
							static::$users[$userIdent]['city'] = $location->Address->City;
						}
						if(isset($location->Address->State)) {
							static::$users[$userIdent]['state'] = $location->Address->State;
						}
						if(isset($location->Address->AdditionalData)) {
							foreach($location->Address->AdditionalData as $keyValue) {
								if($keyValue->key == 'CountryName') {
									static::$users[$userIdent]['country'] = $keyValue->value;
									break;
								}
							}
						}
						if(isset($location->Address->Label)) {
							static::$users[$userIdent]['map_address'] = $location->Address->Label;
						}
					}
					if(isset($location->DisplayPosition)) {
						if(isset($location->DisplayPosition->Latitude)) {
							static::$users[$userIdent]['lat'] = $location->DisplayPosition->Latitude;
						}
						if(isset($location->DisplayPosition->Longitude)) {
							static::$users[$userIdent]['lng'] = $location->DisplayPosition->Longitude;
						}
					}
				}
				unset(static::$users[$userIdent]['geo']);
				if(!static::saveUser(static::$users[$userIdent], $userIdent)) return false;

				break;

			case 'update_userlink':
				$user = static::filterContent($response, '#agents-listing a');
				$save = '';
				if($user->count() > 0) {
					$href = $user->eq(0)->attr('href');
					if($href && strlen($href) > 5) {
						$link = static::$url.$href;
						$log = ParserLog::where([['entity_id', $request->params['property_id']], ['id', $request->params['log_id']]])->update(['message' => 'UserLink: '.$link]);
						$save = 'SAVE';
					}
				} else if($request->repeat < 4) {
					$request->repeat++;
					$curl->add($request);
					echo '->'.$request->repeat.PHP_EOL;
					return true;
				}
				if(empty($save)) {
					$log = ParserLog::where([['entity_id', $request->params['property_id']], ['id', $request->params['log_id']]])->update(['message' => 'NoUserLink']);
				}
				static::$parsed++;
				echo static::$parsed.'. update_userlink '.$user->count().$save.PHP_EOL;
				break;
		}

		return true;
	}
	public static function saveImage(&$fileName, $data, $info, $minSize = 1000) {
		if(file_exists($fileName)) {
			unlink($fileName);
		}
		$len = $info['download_content_length'];
		if($len > 0 && $len < 100000 && in_array(md5($data), static::$leerImages)) return 0;

		if(!file_put_contents($fileName, $data)) return false;
		chmod($fileName, 0777);

		$size = filesize($fileName);
		if($size == 0 || $size < $minSize) return false;

		if($info['size_download'] != $size || $len > $size) return false;

		$is = @getimagesize($fileName);
		if(!$is || !isset(static::$imageTypes[$is[2]])) return false;
		if($is[2] != 2) {
			//echo $fileName;
			//print_r($is);
			Image::make($fileName)->encode('jpg', 100)->save($fileName);

			/*$oldFile = $fileName;
			$fileName = str_replace('.jpg', static::$imageTypes[$is[2]], $oldFile);
			if(!rename($oldFile, $fileName)) return false;*/
		}
		//elseif (!in_array($is[2], array(1,2,3))) return false
		return $size;
	}

	public static function saveProperty($data, $ident, $reqBlock)
	{
		echo 'wait=>'.sizeof(static::$properties).' save=>'.$ident.' | ';

		$log = $data['log'];
		print_r($log);

		if(isset($data['images']) && sizeof($data['images']) > 0) {
			$images = static::moveImages($data['images']);
			$cnt = sizeof($images) + $data['leer_images'];
			if($cnt == 0) {
				static::$imagesErrors++;
				static::setError('Images Load Error: '.$data['cnt_images'], false, $log);
			} else {
				if($cnt < $data['cnt_images']) {
					$log['message'] = 'Not all Images loaded: '.$cnt.'/'.$data['cnt_images'];
				}
				if(sizeof($images) > 0) {
					$data['photos'] = $images;
				}
			}
		}

		echo isset($data['photos']) ? sizeof($data['photos']) : 0;

		static::saveNewProperty($data, $log);

		static::$parsed++;
		static::$blocks[$reqBlock]['parsed']++;
		echo ' parsed='.(static::$blocks[$reqBlock]['parsed']).'|'.static::$parsed.PHP_EOL;
		if(!empty(static::$parseLimit) && static::$parsed >= static::$parseLimit) return false;

		if(!empty($ident)) {
			unset(static::$properties[$ident]);
		}

		if(static::$parsed > 4000) {
			Setting::setValue('parsers', 'zezoom_proxies', json_encode(static::$curl->getAliveProxies()));
			static::createNewJob();
		}

		return true;
	}

	public static function saveUser($data, $ident)
	{
		if(isset($data['geo']) || (isset($data['image']) && empty($data['image'])) || !isset($data['log'])) return true;

		echo 'USER wait=>'.sizeof(static::$users).' save=>'.$ident.' | ';

		//print_r($data);
		$log = $data['log'];
		$userId =  static::isParsed($log, 1);

		if(!$userId) {
			if(isset($data['image'])) {
				$images = static::moveImages([$data['image']]);
				if(sizeof($images) > 0) {
					$data['photo'] = $images[0];
				}
			}

			echo isset($data['photo']) ? 'PHOTO' : '';
			$userId = static::saveNewUser($data, $log, [], false);
			static::$parsedUsers++;
			echo ' | '.static::$parsedUsers;
		}
		if(isset($data['property_id'])) {
			static::updateAuthor($data['property_id'], $userId, isset($data['log_id']) ? $data['log_id'] : 0);
		}

		if(!empty($ident)) {
			unset(static::$users[$ident]);
		}

		return true;
	}

	public static function updateAuthors($countries)
	{
		$curl = static::$curl;
		$limit = 1000;
		$round = 1;
		$all = 0;
		do {
			echo '------UPDATE AUTHORS '.$round.'------'.PHP_EOL;

			$properties = Property::select(DB::raw('properties.id, p.message, properties.city, properties.state, properties.country, p.id as log_id'))
				->join('parser_log as p', 'p.entity_id', '=', 'properties.id')
				->where([['p.parser_id', 2], ['p.entity_type', 2], ['p.result', 1]])
				->where('p.message', 'like', 'UserLink: %')
				->limit(20000)
				->get();

			$properties = $properties ? $properties->toArray() : [];

			$cntProperties = sizeof($properties);
			echo 'count='.$cntProperties.PHP_EOL;
			if($cntProperties == 0) break;

			$cnt = 0;
			$num = 0;
			static::$users = [];
			static::$userIdent = 0;
			static::controlStopping();
			$doubles = [];
			$loaded = 0;

			foreach($properties as $property) {
				$num++;
				echo $num.'. ';
				$link = trim(substr($property['message'], 10));
				if(strlen($link) > 0) {
					$propertyId = $property['id'];
					$logId = $property['log_id'];
					$log = ['method' => 'get', 'url' => $link];

					$userId = static::isParsed($log, 1);

					if($userId) {
						static::updateAuthor($propertyId, $userId, $logId);
						echo 'foundId='.$userId.PHP_EOL;
					} elseif(!isset($doubles[$link])) {
						$cnt++;
						$loaded++;
						$country = $property['country'];
						if(isset(static::$errorContries[$country])) {
							$country = static::$errorContries[$country];
						}
						$curl->get($link, null, null, ['type' => 'user', 'property_id' => $propertyId, 'log_id' => $logId, 'log' => $log, 'city' => $property['city'], 'state' => $property['state'], 'country' => (isset($countries[$country]) ? $countries[$country] : null), 'block' => 0]);
						$doubles[$link] = $propertyId;
						echo 'load '.$cnt.'->'.$link.PHP_EOL;
					} else {
						echo 'DB '.PHP_EOL;
					}

				}

				if($cnt >= $limit || $num >= $cntProperties) {
					if(!$curl->execute(100)) return true;

					foreach(static::$users as $ident => $data) {
						if(isset($data['geo'])) {
							static::setError('User Geocode not found', false, $data['log']);
							continue;
						}
						if(isset($data['image']) && empty($data['image'])) {
							unset($data['image']);
						}
						if(!static::saveUser($data, $ident)) return false;
					}
					static::controlStopping();
					$curl->__set('requests', []);
					static::$users = [];
					static::$userIdent = 0;
					$cnt = 0;
				}
			}
			$all += $cntProperties;
			$round++;
			echo 'LOADED='.$loaded.PHP_EOL;
		} while ($round < 20 && $cntProperties > 0);
		return true;
	}

	public static function updateAuthor($id, $author, $logId = 0) {
		Property::where('id', $id)->update(['author' => $author]);
		$log = ParserLog::where([['entity_id', $id], ['parser_id', 2], ['entity_type', 2], ['result', 1]]);
		if(!empty($logId)) {
			$log->where('id', $logId);
		}
		$log->update(['message' => 'Set User:'.$author]);
	}

	public static function runUpdate()
	{
		$curl = static::$curl;
		$limit = 2000;
		$round = 1;
		$all = 0;
		do {
			echo '------UPDATE USERLINKS '.$round.'------'.PHP_EOL;
			$properties = Property::select(DB::raw('properties.id, p.url, p.id as log_id'))
				->join('parser_log as p', 'p.entity_id', '=', 'properties.id')
				->where([['properties.author', 1], ['p.parser_id', 2], ['p.entity_type', 2], ['p.result', 1], ['p.message', null]])
				->limit(20000)
				->get();

			$properties = $properties ? $properties->toArray() : [];

			$cntProperties = sizeof($properties);
			echo 'count='.$cntProperties.PHP_EOL;
			if($cntProperties == 0) return true;

			$cnt = 0;
			$num = 0;
			static::controlStopping();

			foreach($properties as $property) {
				$num++;
				$propertyId = $property['id'];
				$logId = $property['log_id'];
				$link = $property['url'];
				if(!is_null($link) && strlen($link) > 0) {
					$cnt++;
					$curl->get($link, null, null, ['type' => 'update_userlink', 'property_id' => $propertyId, 'log_id' => $logId, 'block' => 0]);
					if($cnt >= $limit || $num >= $cntProperties) {
						if(!$curl->execute(100)) return true;
						echo 'EXEC'.PHP_EOL;

						static::controlStopping();
						sleep(static::$bigTimeout);
						$curl->__set('requests', []);
						$cnt = 0;
					}
				}
			}
			$round++;
			$all += $cntProperties;
		} while ($round < 30 && $all < 250000 && $cntProperties > 0);
		return true;
	}

	public static function deleteBadImages()
	{
		$badImages = ['c9281dc58d5ee5b74f795f47b961dd75', '4a1d7a03849fdbc261067559105a07a8', 'd8eb5f3abdc0d4b8f59e219bf4b11929',
		 '79828a04a3279b03ab9aec12324c73d9', '0507951e91eb571d5c676f480d8dd088', '86c5f0dbde6da36fb496592f767972c2'];
		$round = 1;
		$all = 0;
		$last = 451080;
		$last = 3791126;
		//$last = 4150996;
		$cnt = 0;
		$uploadsPath = Upload::getUploadsPath();
		do {
			echo '------DELETE BAD IMAGES '.$round.'------'.PHP_EOL;
			$uploads = Upload::select(DB::raw('id, name'))
				->where('type', 1)
				->where('id', '>', $last)
				->orderBy('id')
				->limit(20000)
				->get();

			$uploads = $uploads ? $uploads->toArray() : [];

			$cntUploads = sizeof($uploads);
			echo 'count='.$cntUploads.PHP_EOL;
			if($cntUploads == 0) break;

			$num = 0;
			static::controlStopping();

			foreach($uploads as $upload) {
				$num++;
				$id = $upload['id'];
				$path = $uploadsPath . '/' . $upload['name'];
				$data = file_get_contents($path);
				//echo $num;
				if(in_array(md5($data), $badImages)) {
					$cnt++;
					ParserLog::where([['entity_id', $id], ['entity_type', 3], ['parser_id', 2], ['result', 1]])->update(['message' => 'BadImage']);
					Upload::deleteUpload(null, $id);
					UploadProperty::where('upload_id', $id)->delete();

					echo $cnt.' BAD IMAGE '.$id.PHP_EOL;
				}
				//echo PHP_EOL;
				//if($cnt > 20) return true;
				//if($num > 10000) return true;
				$last = $id;
			}
			$round++;
			$all += $cntUploads;
		} while ($round < 100 && $cntUploads > 0);
		echo '$last='.$last.PHP_EOL;
		echo '$all='.$all.PHP_EOL;
		echo '$cnt='.$cnt.PHP_EOL;

		return true;
	}

	public static function deleteAllImages()
	{
		$round = 1;
		$all = 0;
		do {
			echo '------DELETE All IMAGES '.$round.'------'.PHP_EOL;
			$uploads = ParserLog::select(DB::raw('u.id'))
				->join('uploads as u', 'u.id', '=', 'parser_log.entity_id')
				->where([['parser_log.parser_id', 2], ['parser_log.entity_type', 3], ['parser_log.result', 1]])
				->limit(20000)
				->get();
			$uploads = $uploads ? $uploads->toArray() : [];

			$cntUploads = sizeof($uploads);
			echo 'count='.$cntUploads.PHP_EOL;
			if($cntUploads == 0) break;
			static::controlStopping();

			foreach($uploads as $upload) {
				Upload::deleteUpload(null, $upload['id']);
			}
			$round++;
			$all += $cntUploads;
		} while ($round < 1000 && $cntUploads > 0);
		echo 'deleted='.$all.PHP_EOL;

		return true;
	}

	public static function deleteAllImagesForDeletedProperties()
	{
		$round = 1;
		$all = 0;
		do {
			echo '------DELETE All IMAGES '.$round.'------'.PHP_EOL;
			$uploads = DB::select('select u.id
from uploads u
inner join uploads_properties d on (d.upload_id=u.id)
where not exists(select 1 from properties p where p.id=d.property_id) LIMIT 20000');

			$cntUploads = sizeof($uploads);
			echo 'count='.$cntUploads.PHP_EOL;
			//echo 'cnt='.$uploads[0]->cnt.PHP_EOL;
			//return true;
			if($cntUploads == 0) break;
			static::controlStopping();

			foreach($uploads as $upload) {
				//echo $upload['id'].'->'.$upload['property_id'].PHP_EOL;
				Upload::deleteUpload(null, $upload->id);
			}
			$round++;
			$all += $cntUploads;
			//return true;

		} while ($round < 1000 && $cntUploads > 0);
		echo 'deleted='.$all.PHP_EOL;

		return true;
	}
}

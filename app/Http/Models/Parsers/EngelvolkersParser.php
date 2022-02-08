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

class EngelvolkersParser extends BaseParser
{
	public static $parserParams = ['index', 'business'];
	public static $userName = '';
	//public static $userEmail = 'yelp@parsed-';
	public static $userEmail = 'info@medicaleer.com';
    public static $imageName = 'medicaleer_ev_';
	public static $parseLimit = 0;
    public static $maxBlocks = 3;
	public static $hashFields = ['url'];
    public static $propTypes = ['Apartment' => 1, 'Byt' => 1, 'Appartement' => 1, 'Wohnung' => 1, 'Apartamento' => 1, 'Διαμέρισμα' => 1, 'Appartamento' => 1, 'Lakás' => 1, 'Кварти́ра' => 1,
        'Land' => 11, 'Land- und Forstwirtschaft' => 11, 'Grundstück' => 11, 'Terreno' => 11, 'Bouwgrond' => 11, 'Γή' => 11, 'Terrain' => 11,
        'House' => 2, 'Moradia' => 2, 'Telek' => 2, 'Haus' => 2, 'Ház' => 2, 'Huis' => 2, 'Casa' => 2, 'Villa' => 2, 'Neubauprojekt' => 2, 'Chalet' => 2, 'Obra nueva' => 2, 'Maison' => 2, 'Castle' => 2, 'Business office' => [6, 1],
        'Residential building' => 2, 'Inversión / Residencial inversión' => 2, 'Investment / Residential investment' => 2, 'Bungalow' => 2, 'Garage parking' => [6, 10],
        'Other' => 15, 'Otros' => 15, 'Sonstige' => 15, 'Altri' => 15, 'Projets Neuf' => 15,
        'Farm' => [6, 9], 'Commercial' => 6, 'Industry' => [6, 4], 'Industrial building' => [6, 4], 'Investment / Wohn- und Geschäftshäuser' => [6, 1], 'Bürofläche' => [6, 1], 'Investimenti / Investimento residenziale' => [6,1],
        'Industria / Almacén / Producción' => [6, 4], 'Industrie / Lagerhallen / Produktion' => [6, 4], 'Industria / Magazzino / Produzione' => [6, 4],
        'Industry / Warehouse/ Production' => [6, 4], 'Industrie/Lagerhallen/Produktion' => [6, 4], 'Boutique' => [6,4], 'Serviços de retalho' => [6,4],
        'Hotel' => [6, 7], 'Ladenfläche' => [6, 7], 'Albergo' => [6,7],
        'Office' => [6,1], 'Offices' => [6,1], 'Oficina' => [6,1], 'Ufficio' => [6,1], 'Bureau' => [6,1],
        'Retail Services' => [6,3], 'Retail' => [6,3], 'Servicios retail' => [6,3], 'Servizi di vendita al dettaglio' => [6,3], 'Showrooms' => [6,5], 'Showroom' => [6,5],
        'Developments' => [6,6], 'Nieuwbouwproject' => [6,6], 'Empreendimentos' => [6,6], 'Új építésű projekt' => [6,6], 'Nuovo progetto edilizio' => [6,6],
        'Farms' => [6,9], 'Medical' => [6,8]];
    public static $properties = [];
    public static $propertyIdent = 0;
    public static $parseBlockLimit = 1000;
    public static $parsedBlocks = 0;
    public static $imageIdent = 0;
	public static $location = null;
	public static $blocks = [];
	public static $startIndex = 0;
	public static $startIndexSize = 18;
	public static $lastIndex = 0;
    public static $leerImages = [
        '52ce81df817053b98c62379d93a3e6bb', '6b243f62d70373eeade2f2cb5866c678',
        '95521009dfa769f353ac6d07e583dee6', 'bc09560da3ad29f7b3d9b7d04169b7c0',
        '0bbd03e6ab8dae1f72593108207f2aab', '6b1b95297363168987eab153d8b0ef6a',
        '0e74e5a5f11a66beaf2bb6eec5cd48df', 'e3ac255e38abe0089afa0fbcafe3a5b3',
        '9df30da6015cd9e4351122619e6e9464', '86c5f0dbde6da36fb496592f767972c2'];
    public static $imageTypes = [1 => '.gif', 2 => '.jpg', 3 => '.png'];
    public static $imagesErrors = 0;
    public static $imagesErrorLimit = 10000;
    public static $windowSize = 2;
    public static $userId = 2743290; // EV agency
    private static $start = LARAVEL_START;
    
    public static $errorContries = ['Russia' => 177];
    
    
    public static function setLocation($location)
	{
		static::$location = $location;
	}
    
    public static function setStartIndex($index)
    {
        static::$startIndex = $index;
    }
    
    public static function getStartIndex()
    {
        return static::$startIndex;
    }

	public static function run(Parser $parser)
	{
        //https://www.engelvoelkers.com/en/search/?q=&startIndex=0&businessArea=residential&sortOrder=DESC&sortField=sortPrice&pageSize=18&facets=bsnssr%3Aresidential%3B
		//https://www.engelvoelkers.com/en/search/?q=&startIndex=0&businessArea=commercial&sortOrder=DESC&sortField=sortPrice&pageSize=18&facets=bsnssr%3Acommercial%3B
        
        $url = $parser->url;
		static::setUrl($url);
		$model = $parser->model;
		$params = static::getParams($model);
		
		static::deleteProperties($params); return true;
		
        static::prepareTempDir();
        static::$imageName .= date('YmdHis').'_';
		
		$search = $url.'/en/search/';
		static::$parsed = 0;
		$curl = new RollingCurl([$model . 'Parser', 'parse']);
        
        $curl->setCookie('cookie: _icl_current_language=en; evlocale=en_US; engelundvoelkersconfig=USD-sqft.ft-true-US-en');
        $curl->setAuth('Selssaltovsk1:V6w0OcI');
        $curl->setHostIp(gethostbyname('medicaleer.com'));
        $curl->setUserAgents([
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.71",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.88",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko"]);
		$curl->setProxies(static::getProxyList(), 'http', 'https://www.engelvoelkers.com/', 'engelvoelkers');
		echo 'Set curl'.PHP_EOL;
		static::setCurl($curl);
		$cntBlocks = 0;
        
        //static::runUpdateLocation(); return true;
		
		$currentIndex = $params['index'];
		static::setStartIndex($currentIndex);
		static::$lastIndex = $currentIndex + static::$startIndexSize * static::$parseBlockLimit;
        
        $blockNum = 0;
        foreach ($params['business'] as $business) { // only commercial, residental index 142
            
            while($currentIndex <= static::$lastIndex) {
                $block = $currentIndex.'|'.$business;
                echo 'Before get parser result '.$block.PHP_EOL;
                if(!static::getParserResult($block)) {
                    echo 'Is not parsed '.$block.PHP_EOL;
                    $blockNum++;
                    static::$blocks[$blockNum] = ['parsed' => 0, 'block' => $block];
            
                    $link = $search.'?q=&startIndex='.$currentIndex.'&businessArea='.$business.'&sortOrder=DESC&sortField=sortPrice&pageSize=18&facets=bsnssr%3A'.$business.'%3Btyp%3Arent%3B';
                    $curl->get($link, null, null, ['type' => 'search', 'business' => $business, 'property_type' => 'rent', 'url' => $link, 'block' => $blockNum]);
                    $link = $search.'?q=&startIndex='.$currentIndex.'&businessArea='.$business.'&sortOrder=DESC&sortField=sortPrice&pageSize=18&facets=bsnssr%3A'.$business.'%3Btyp%3Abuy%3B';
                    $curl->get($link, null, null, ['type' => 'search', 'business' => $business, 'property_type' => 'buy', 'url' => $link, 'block' => $blockNum]);
                }
    
                $currentIndex = $currentIndex == 0 ? $currentIndex + 16 : $currentIndex + static::$startIndexSize;
        
                if($blockNum >= static::$maxBlocks) {
                    static::$properties = [];
                    static::$propertyIdent = 0;
                    echo 'Start: '.PHP_EOL;
                    if(!$curl->execute(static::$windowSize)) return true;
    
                    Setting::setValue('parsers', 'engelvolkers_index', $currentIndex);
                    
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
                        
                        static::setParsedResults($data['block'], ['done' => 1, 'parsed' => $data['parsed']]);
                        static::$parsedBlocks++;
                    }
                    echo ' parsedBlocks='.static::$parsedBlocks.PHP_EOL;
                    if(!empty(static::$parseBlockLimit) && static::$parsedBlocks >= static::$parseBlockLimit) return true;
                    
                    static::controlStopping();
                    if(static::$parsed > 1500) {
                        Setting::setValue('parsers', 'engelvolkers_proxies', json_encode($curl->getAliveProxies()));
                        static::createNewJob();
                    }
                    //sleep(static::$bigTimeout);
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

		if($reqType == 'search') {
			static::controlStopping();

			$links = static::filterContent($response,'.ev-search-results a.ev-property-container', '.row.ev-search-results > div > a');

			$contentCount = $links->count();
			echo 'search:'.$contentCount;
			if($contentCount == 0 && $request->repeat < 5) {
				$request->repeat++;
				$curl->add($request);
				echo '->'.$request->repeat;
			}
			echo PHP_EOL;

			foreach($links as $node) {
                $link = $node->getAttribute('href');
                if(!$link || empty($link) || strpos($link, 'en-ru/')) continue;
                
                $log = ['method' => 'get'];
                $parts = explode('?', $link, 2);
                $log['url'] = $link;
                if(sizeof($parts) > 1) {
                    $log['params'] = $parts[1];
                }
                $entityId = static::isParsed($log, 2);
                if($entityId) {
                    static::$blocks[$reqBlock]['parsed']++;
                    echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
                } else {
                    $curl->get($link, null, null, ['type' => 'property', 'property_type' => $request->params['property_type'], 'business' => $request->params['business'], 'url' => $link, 'block' => $reqBlock]);
                }
			}
			//dd($curl);
		} elseif($reqType == 'property') {
            $reqPropType = $request->params['property_type'];
            $reqBusiness = $request->params['business'];
            $contentTitle = static::filterContent($response, 'h1.ev-exposee-title');
            $contentSubtitle = static::filterContent($response, 'div.ev-exposee-subtitle');
            $contentFacts = static::filterContent($response, 'div.ev-key-facts');
			$content = static::filterContent($response, 'h2.ev-exposee-title + .ev-exposee-detail-facts, .ev-exposee-text');
            $contentImages = static::filterContent($response, 'a.ev-image-gallery-image-link > img.ev-image-gallery-image:not(.ev-stretch-image)');
			
            echo 'property:'.$content->count();
            echo 'propertyImages:'.$contentImages->count();

			$contentCount = $content->count();
			if($contentCount < 2 && $request->repeat < 5) {
				$request->repeat++;
				$curl->add($request);
				echo '->'.$request->repeat;
			}
			echo PHP_EOL;
            
            $data = [];
			if ($contentTitle->count()) {
                static::$propertyIdent++;
                $node = $contentTitle->eq(0);
                $data['title'] = trim($node->text());
                if(empty($data['title'])) return true;
            } else {
			    return true;
            }
            
            if ($contentSubtitle->count()) {
                $node = $contentSubtitle->eq(0);
                $subtitleAddress = static::getNodeText($node,
                    '.ev-exposee-subtitle', false);
                $subtitleAddress = explode('|',
                    $subtitleAddress);
                $data['address'] = $data['map_address'] = trim(end($subtitleAddress));
                
                $link = 'https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext='.str_replace(' ', '%20', $data['address']).'&gen=8&apiKey=Al-pQcxe5clM9f_03bItLzPNrCLLC3Wg5L5j1ZeWVaY&language=en-US';
                $curl->get($link, null, null, ['type' => 'geosearch', 'num' => 1, 'prop_ident' => static::$propertyIdent, 'block' => $reqBlock]);
                
                if (count($subtitleAddress) > 1) {
                    $propType = $subtitleAddress[0];
                    if (strpos($propType, ',') !== false) {
                        $propType = explode(',', $propType);
                        $propType = trim($propType[0]);
            
                        if (isset(static::$propTypes[$propType])) {
                            $typeSubType
                                = static::$propTypes[$propType];
                            if (is_array($typeSubType)) {
                                $data['property_type']
                                    = $typeSubType[0];
                                $data['property_subtype']
                                    = $typeSubType[1];
                            } else {
                                $data['property_type']
                                    = $typeSubType;
                            }
                        } else {
                            $data['property_type'] = $reqBusiness == 'commercial' ? 6 : 2;
                            static::setError('New property type: '
                                .$propType, true,
                                static::getUrlParts($request->url));
                        }
                    }
                }
            }
            
            if ($contentFacts->count()) {
                $node = $contentFacts->eq(0);
                $features = $node->filter('.ev-key-fact');
                $featuresCnt = $features->count();
                if($featuresCnt > 0) {
                    for($n = 0; $n < $featuresCnt; $n++) {
                        $feature = $features->eq($n);
                        if (false !== strpos($feature->attr('class'),'show-tablet')) {
                            continue;
                        }
                        $icon = $feature->filter('img.ev-key-fact-icon')->count() ?
                            trim($feature->filter('img.ev-key-fact-icon')->eq(0)->attr('src'))
                            : '';
                        $icon = !empty($icon) ? explode('/', $icon) : $icon;
                        !is_array($icon) && $icon = array($icon);
                        $icon = end($icon);
                        $name = $feature->filter('.ev-key-fact-title')->count() ?
                            strtolower(trim($feature->filter('.ev-key-fact-title')->eq(0)->text()))
                            : '';
                        $value = $feature->filter('.ev-key-fact-value')->count() ?
                            strtolower(trim($feature->filter('.ev-key-fact-value')->eq(0)->text()))
                            : '';
                        if($feature->count() && $name && $value) {
                            switch ($icon){
                                case 'Icon_AvailableFrom.svg':
                                case 'Icon_NumberOfUnits.svg':
                                    break;
                                case 'Icon_Bedrooms.svg':
                                case 'Icon_Rooms.svg':
                                    $data['bedrooms'] = intval($value);
                                    break;
                                case 'Icon_Bathrooms.svg':
                                    $data['bathrooms'] = intval($value);
                                    break;
                                case 'Icon_LivingSpace.svg':
                                case 'Icon_TotalSurface.svg':
                                    $ac = strpos($value, ' ac') !== false ? 1 : 0;
                                    $meter = strpos($value, ' m') !== false ? 1 : 0;
                                    $comma = strpos($value, ',');
                                    $replaceSign = $comma && strpos($value, '.') < $comma ? '.' : ',';
                                    $value = str_replace(array(' ',$replaceSign,'ac','sqft','m²'), '', $value);
                                    $value = $comma ? str_replace(',', '.', $value) : $value;
                                    $data['property_area'] = floatval($value);
                                    $data['property_area_measure'] = 2;
                                    if ($ac) {
                                        $data['property_area'] = $data['property_area'] * 43560; //convert to sqft
                                    } elseif ($meter) {
                                        $data['property_area_measure'] = 1;
                                    }
                                    break;
                                case 'Icon_PropertyArea.svg':
                                    $ac = strpos($value, ' ac') !== false ? 1 : 0;
                                    $meter = strpos($value, ' m') !== false ? 1 : 0;
                                    $comma = strpos($value, ',');
                                    $replaceSign = $comma && strpos($value, '.') < $comma ? '.' : ',';
                                    $value = str_replace(array(' ',$replaceSign,'ac','sqft','m²'), '', $value);
                                    $value = $comma ? str_replace(',', '.', $value) : $value;
                                    $data['land_area'] = floatval($value);
                                    $data['land_area_measure'] = 2;
                                    if ($ac) {
                                        $data['land_area'] = $data['land_area'] * 43560; //convert to sqft
                                    } elseif ($meter) {
                                        $data['land_area_measure'] = 1;
                                    }
                                    break;
                                case 'Icon_Price.svg':
                                    $value = trim($value);
                                    $usd = strpos($value, 'usd') !== false ? 1 : 0;
                                    if(!$usd && $feature->filter('.ev-key-fact-converted')->count()) {
                                        $value = strtolower(trim($feature->filter('.ev-key-fact-converted')->eq(0)->text()));
                                        $value = floatval(str_replace(array('usd',' ','approx.',',','aprox.'), '', $value));
                                    } else {
                                        $value = str_replace(array(','), '', $value);
                                        $value = strlen($value) >= 6 ? str_replace(array('.'), '', $value) : $value;
                                        $value = explode(' ', $value);
                                        $value = floatval(array_shift($value));
                                    }
                                    $data['price'] = ($value && is_numeric($value)) ? $value : 1;
                                    break;
                                default:
                                    static::setError('New feature: '.$icon.' => '.$value, true, static::getUrlParts($request->url));
                                    return true;
                                    break;
                            }
                        }
                    }
                }
            }

			if($contentCount) {
                for ($n = 0; $n < $contentCount; $n++) {
                    $node = $content->eq($n);
                    switch ($node->nodeName()) {
                        case 'ul':
                            if (isset($data['descriptions'])) {
                                $data['descriptions']['en'] .= '<br>'
                                    .trim($node->text());
                            } else {
                                $data['descriptions'] = [];
                                $data['descriptions']['en']
                                    = trim($node->text());
                            }
                            break;
                        case 'p':
                            if (isset($data['descriptions'])) {
                                $data['descriptions']['en'] .= '<br>'
                                    .trim($node->text());
                            } else {
                                $data['descriptions'] = [];
                                $data['descriptions']['en']
                                    = trim($node->text());
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            
            $data['images'] = [];
            $data['cnt_images'] = $imagesCnt = $contentImages->count();
            if($imagesCnt >= 1) {
                for($n = 0; $n < $imagesCnt; $n++) {
                    static::$imageIdent++;
                    $link = $contentImages->eq($n)->attr('src');
                    if(!$link || strlen($link) < 10) continue;
                    echo 'Img link: '.$link.PHP_EOL;
                    $end = strpos($link, '?w=');
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
                $data['leer_images'] = 0;
                echo 'images='.$data['cnt_images'].PHP_EOL;
            }
            
            $data['author'] = static::$userId;
            $data['status'] = 1;
            $data['currency_code'] = 840;
            $data['property_status'] = $reqPropType == 'buy' ? 2 : 1;
            $data['log'] = ['method' => 'get', 'url' => $request->params['url']];
            
//            if(!isset($data['cnt_images']) || empty($data['cnt_images'])) {
//                if(!static::saveProperty($data, 0, $reqBlock)) return false;
//            } else {
                static::$properties[static::$propertyIdent] = $data;
//            }
        } elseif($reqType == 'image') {
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
            
//            if(empty(static::$properties[$propertyIdent]['cnt_images'])) {
//                if(!static::saveProperty(static::$properties[$propertyIdent], $propertyIdent, $reqBlock)) return false;
//            }
        } elseif ($reqType == 'geosearch') {
            $propertyIdent = $request->params['prop_ident'];
            $num = $request->params['num'];
            $num++;
            
            $output = json_decode($response);
            $isAddressed = 0;
            if ($output && !empty($output->Response)) {
                if (isset($output->Response->View)
                    && !empty($output->Response->View)
                    && !empty($output->Response->View[0]->Result)
                ) {
                    $isAddressed = 1;
                    $location
                        = $output->Response->View[0]->Result[0]->Location;
                    echo 'Location:'.PHP_EOL;
                    print_r($location);
                    static::$properties[$propertyIdent]['lat'] = $location->DisplayPosition->Latitude;
                    static::$properties[$propertyIdent]['lng']
                        = $location->DisplayPosition->Longitude;
                    static::$properties[$propertyIdent]['country']
                        = $location->Address->Country; //iso3
                    static::$properties[$propertyIdent]['state'] = isset($location->Address->State)
                        ? $location->Address->State : '';
                    static::$properties[$propertyIdent]['city'] = isset($location->Address->City)
                        ? $location->Address->City : '';
                    static::$properties[$propertyIdent]['postal_code']
                        = isset($location->Address->PostalCode)
                        ? $location->Address->PostalCode : '';
                    
                    if (!empty(static::$properties[$propertyIdent]['country'])) {
                        static::$properties[$propertyIdent]['country']
                            = Country::getCountryId(static::$properties[$propertyIdent]['country'],
                            'iso3');
                        
                        if (in_array(static::$properties[$propertyIdent]['country'], static::$errorContries)) {
                            return false;
                        }
                    }
                }
            }
            
            if (!$isAddressed && !empty(static::$properties[$propertyIdent]['address']) && $num < 3) {
                $address = array_map(function($item){
                    return trim($item);
                },explode(',', static::$properties[$propertyIdent]['address']));
                
                array_pop($address);
                $address = implode(' ', $address);
                $link = 'https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext='.str_replace(' ', '%20', $address).'&gen=8&apiKey=Al-pQcxe5clM9f_03bItLzPNrCLLC3Wg5L5j1ZeWVaY&language=en-US';
                $curl->get($link, null, null, ['type' => 'geosearch', 'num' => $num, 'prop_ident' => $propertyIdent, 'block' => $reqBlock]);
            }
        } elseif ($reqType == 'update_location') {
            $propertyId = $request->params['property_id'];
            $propertyAddress = $request->params['address'];
            $num = $request->params['num'];
            $num++;
            
            $output = json_decode($response);
            $isAddressed = 0;
            static::$properties[$propertyId] = [];
            if ($output && !empty($output->Response)) {
                if (isset($output->Response->View)
                    && !empty($output->Response->View)
                    && !empty($output->Response->View[0]->Result)
                ) {
                    $isAddressed = 1;
                    $location
                        = $output->Response->View[0]->Result[0]->Location;
                    static::$properties[$propertyId]['lat'] = $location->DisplayPosition->Latitude;
                    static::$properties[$propertyId]['lng']
                        = $location->DisplayPosition->Longitude;
                    static::$properties[$propertyId]['country']
                        = $location->Address->Country; //iso3
                    static::$properties[$propertyId]['state'] = isset($location->Address->State)
                        ? $location->Address->State : '';
                    static::$properties[$propertyId]['city'] = isset($location->Address->City)
                        ? $location->Address->City : '';
                    static::$properties[$propertyId]['postal_code']
                        = isset($location->Address->PostalCode)
                        ? $location->Address->PostalCode : '';
                    
                    if (!empty(static::$properties[$propertyId]['country'])) {
                        static::$properties[$propertyId]['country']
                            = Country::getCountryId(static::$properties[$propertyId]['country'],
                            'iso3');
                        
                        if (in_array(static::$properties[$propertyId]['country'], static::$errorContries)) {
                            return false;
                        }
                    }
                }
            }
            
            if (!$isAddressed && !empty($propertyAddress) && $num < 5) {
                $address = array_map(function($item){
                    return trim($item);
                },explode(',', $propertyAddress));
                
                array_pop($address);
                $address = implode(', ', $address);
                $link = 'https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext='.str_replace(' ', '%20', $address).'&gen=8&apiKey=Al-pQcxe5clM9f_03bItLzPNrCLLC3Wg5L5j1ZeWVaY&language=en-US';
                $curl->get($link, null, null, ['type' => 'update_location', 'num' => $num, 'property_id' => $propertyId, 'address' => $address, 'block' => $reqBlock]);
            }
        }

		return true;
	}
    
    public static function saveProperty($data, $ident, $reqBlock)
    {
        echo 'wait=>'.sizeof(static::$properties).' save=>'.$ident.' | ';
        
        $log = $data['log'];
        print_r($log);
    
        $entityId = static::isParsed($log, 2);
        if($entityId) {
            static::$blocks[$reqBlock]['parsed']++;
            echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
            if(!empty($ident)) {
                unset(static::$properties[$ident]);
            }
            return true;
        }
        
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
            Setting::setValue('parsers', 'engelvolkers_proxies', json_encode(static::$curl->getAliveProxies()));
            static::createNewJob();
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
    
    public static function runUpdateLocation()
    {
        $curl = static::$curl;
        $limit = 100;
        $round = 1;
        $all = 0;
        do {
            echo '------UPDATE LOCATIONS '.$round.'------'.PHP_EOL;
            $properties = Property::select(DB::raw('properties.id, properties.address, p.url, p.id as log_id'))
                ->join('parser_log as p', 'p.entity_id', '=', 'properties.id')
                ->where([['properties.author', static::$userId], ['properties.country', NULL], ['p.parser_id', 3], ['p.entity_type', 2], ['p.result', 1]])
                ->limit(1000)
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
                $propertyAddress = $property['address'];
                if(!is_null($propertyAddress) && strlen($propertyAddress) > 0) {
                    $link = 'https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext='.str_replace(' ', '%20', $propertyAddress).'&gen=8&apiKey=Al-pQcxe5clM9f_03bItLzPNrCLLC3Wg5L5j1ZeWVaY&language=en-US';
                    $cnt++;
                    $curl->get($link, null, null, ['type' => 'update_location', 'num' => 1, 'property_id' => $propertyId, 'address' => $propertyAddress, 'block' => 0]);
                    if($cnt >= $limit || $num >= $cntProperties) {
                        static::$properties = [];
                        if(!$curl->execute(static::$windowSize)) return true;
                        echo 'EXEC'.PHP_EOL;
    
                        if (!empty(static::$properties)) {
                            foreach(static::$properties as $ident => $item) {
                                if(sizeof($item) > 0) {
                                    echo 'Save property '.$ident. PHP_EOL;
                                    print_r($item);
                                    static::updateProperty($ident, $item);
                                }
                            }
                        }
                        
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
    
    public static function runUpdateMeasure()
    {
        $limit = 100;
        $round = 1;
        $all = 0;
        do {
            echo '------UPDATE MEASURES '.$round.'------'.PHP_EOL;
            $properties = Property::where([['author', static::$userId], ['land_area_local', '<', 100], ['land_area_measure', 2]])
                ->limit(5000)
                ->get();
            
            $properties = $properties ? $properties->toArray() : [];
            
            $cntProperties = sizeof($properties);
            echo 'count='.$cntProperties.PHP_EOL;
            if($cntProperties == 0) return true;
            
            $cnt = 0;
            $num = 0;
            static::$properties = [];
            foreach($properties as $property) {
                $num++;
                $propertyId = $property['id'];
                $propertyMeasure = $property['land_area_local'];
                if(!is_null($propertyMeasure) && strpos($propertyMeasure,'.')) {
                    unset($property['land_area_local'],$property['id']);
                    $propertyMeasure = str_replace('.', '', $propertyMeasure);
                    $propertyMeasure = strlen($propertyMeasure) > 4 ? substr_replace ($propertyMeasure, '.', -2, 0) : $propertyMeasure;
    
                    $property['land_area'] = $propertyMeasure;
                    static::$properties[$propertyId] = $property;
                    
                    $cnt++;
                    if($cnt >= $limit || $num >= $cntProperties) {
                        if (!empty(static::$properties)) {
                            foreach(static::$properties as $ident => $item) {
                                if(sizeof($item) > 0) {
                                    echo 'Update property '.$ident. PHP_EOL;
                                    print_r($item);
                                    static::updateProperty($ident, $item);
                                }
                            }
                        }
    
                        static::$properties = [];
                        $cnt = 0;
                    }
                }
            }
            $round++;
            $all += $cntProperties;
        } while ($round < 50 && $all < 5000 && $cntProperties > 0);
        return true;
    }
    
    public static function deleteProperties($params)
    {
        $round = 1;
        $all = 0;
        $last = isset($params['last']) ? $params['last'] : 0;
        $limit = 100;
        $roundMax = 100;
        $cnt = 0;
        
        do {
            echo '------DELETE PROPERTIES '.$round.'------'.PHP_EOL;
            echo 'Start deleting TIME :: '.(microtime(true) - static::$start).PHP_EOL;
            $properties = Property::select('id')
                ->where([['author', static::$userId]])
                ->where('id', '>', $last)
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get();
    
            $properties = $properties ? $properties->toArray() : [];
            
            $cntProperties = sizeof($properties);
            echo 'count='.$cntProperties.PHP_EOL;
            if($cntProperties == 0) break;
            
            $num = 0;
            static::controlStopping();
            
            foreach($properties as $property) {
                $num++;
                $id = $property['id'];
    
                $uploads = UploadProperty::select(DB::raw('upload_id'))
                    ->where('property_id', $id)
                    ->limit(100)
                    ->get();
    
                $uploads = $uploads ? $uploads->toArray() : [];
    
                $cntUploads = sizeof($uploads);
                echo 'count uploads='.$cntUploads.PHP_EOL;
                if ($cntUploads) {
                    foreach ($uploads as $upload) {
                        Upload::deleteUpload(null, $upload['upload_id']);
                        UploadProperty::where('upload_id', $upload['upload_id'])->delete();
                    }
                }
    
                Property::where('id', $id)->delete();
                
                $last = $id;
            }
            $round++;
            $all += $cntProperties;
            Setting::setValue('parsers', 'engelvolkers_last', $last);
        } while ($round < $roundMax && $cntProperties > 0);
        echo '$last='.$last.PHP_EOL;
        echo '$all='.$all.PHP_EOL;
        echo '$cnt='.$cnt.PHP_EOL;
        echo 'END delete TIME :: '.(microtime(true) - static::$start).PHP_EOL;
        
        return true;
    }
}

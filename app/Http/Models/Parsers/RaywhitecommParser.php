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
    
    class RaywhitecommParser extends BaseParser
    {
        public static $parserParams = ['locations'];
        public static $userName = '';
        public static $userEmail = 'info@medicaleer.com';
        public static $imageName = 'medicaleer_rwc_';
        public static $parseLimit = 0;
        public static $parseBlockLimit = 1000;
        public static $parsedBlocks = 0;
        public static $parsedUsers = 0;
        public static $hashFields = ['url'];
        public static $searchUrl = '';
        
        public static $location = null;
        public static $blocks = [];
        public static $maxBlocks = 1;
        
        public static $propStatuses = ['for lease' => 1, 'for sale' => 2, 'For Lease' => 1, 'For Sale' => 2];
        public static $propTypes = [
            'Commercial' => 6,
            'Offices' => [6, 1],
            'Retail' => [6, 3],
            'Industrial' => [6, 4], 'Warehouse' => [6,4], 'Industrial/Warehouse' => [6,4],
            'Showrooms/Bulky Goods' => [6,5], 'Showrooms' => [6,5], 'Bulky Goods' => [6,5], 'OfficesShowrooms/Bulky Goods' => [6,5],
            'Hotel' => [6, 7], 'Hotel/Leisure' => [6,7],
            'Commercial Farming' => [6, 9],
            'Garage parking' => [6, 10],
            'Medical/Consulting' => [6,8], 'Medical' => [6,8], 'Consulting' => [6,8],
            'Land' => [6,6], 'Land/Development' => [6,6],
            'Other' => [6,10], 'Tourism' => [6,10],
            ];
        
        public static $propRentSchedule = ['Daily' => 1, 'Weekly' => 2, 'Per Month' => 3, 'Per Person' => 3, 'Sqm' => 4, 'Psm Pa' => 4, 'Sqm Pa' => 4, 'Per Annum' => 4];
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
        public static $windowSize = 8;
        
        public static $userId = 2743291; // raywhite
        
        public static $iso2 = '';
        
        public static function run(Parser $parser)
        {
            $url = $parser->url;
            static::setUrl($url);
            $model = $parser->model;
            $params = static::getParams($model);
    
            static::prepareTempDir('rwc_temp');
            static::$imageName .= date('YmdHis').'_';
            
            $curl = new RollingCurl([$model . 'Parser', 'parse']);
            $curl->setReferer('https://www.raywhitecommercial.com/');
            $curl->setAuth('Selssaltovsk1:V6w0OcI');
            $curl->setHostIp(gethostbyname('medicaleer.com'));
            $curl->setUserAgents([
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.71",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.88",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0",
                "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko"]);
    
            $curl->setProxies(static::getProxyList(), 'http', 'https://www.raywhitecommercial.com/', 'raywhitecommercial', 700);
            static::setCurl($curl);
            $countries = Country::getCoyntriesForSelect();
            
            static::$parsed = 0;
            $cntBlocks = 0;
            $defResult = ['done' => 0, 'url' => null, 'page' => 0];
            
            foreach($params['locations'] as $location) {
                $city = $location->city;
                $state = $location->state;
                $country = trim($location->country);
                $postcode = $location->postcode;
                
                $find_loc = urlencode($city.', '.(empty($state) ? '' : $state.' ').$postcode);
                $block_loc = $city.','.$state.','.$postcode;
    
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
                    static::$blocks[$blockNum] = ['parsed' => 0, 'block' => $block, 'city' => $city, 'state' => $state, 'country' => $countryId, 'postcode' => $postcode, 'countryName' => $country, 'page' => 0, 'new' => true];
        
                    // https://www.raywhitecommercial.com/search-property/?comtype=ANY&suburb=Sydney%2C+NSW+2000&radius=50&subtype=ANY&auctions=0&floorsizemin=&floorsizemax=&landsizemin=&landsizeunit=m2&landsizemax=
                    if(is_null($parsedResult['url'])) {
                        $link = $url.'/search-property/?comtype=ANY&suburb='.$find_loc.'&radius=50&subtype=ANY&auctions=0&floorsizemin=&floorsizemax=&landsizemin=&landsizeunit=m2&landsizemax=';
                        $curl->get($link, null, null, ['type' => 'list', 'page' => 1, 'url' => $link, 'find_loc' => $find_loc, 'block' => $blockNum]);
                    } else {
                        $link = $parsedResult['url'];
                        $page = is_null($parsedResult['page']) ? 0 : $parsedResult['page'];
                        if($page > 0) {
                            static::$blocks[$blockNum]['page'] = $page;
                            static::$blocks[$blockNum]['new'] = false;
                        }
                        $page++;
                        if ($page > 1) {
                            $link = $url.'/search-property/'.$page.'?comtype=ANY&suburb='.$find_loc.'&radius=50&subtype=ANY&auctions=0&floorsizemin=&floorsizemax=&landsizemin=&landsizeunit=m2&landsizemax=';
                        }
                        $curl->get($link, null, null, ['type' => 'list', 'page' => $page, 'url' => $link, 'find_loc' => $find_loc, 'block' => $blockNum]);
                    }
                    echo '!!!!!'.$link.PHP_EOL;
                }
                if($blockNum >= static::$maxBlocks) {
                    static::$properties = [];
                    static::$propertyIdent = 0;
                    static::$users = [];
                    static::$userIdent = 0;
                    echo 'execute'.PHP_EOL;
                    if(!$curl->execute(static::$windowSize)) return true;
                    echo 'Check blocks '.PHP_EOL;
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
                        Setting::setValue('parsers', 'raywhitecomm_proxies', json_encode($curl->getAliveProxies()));
                        static::createNewJob();
                    }
                    //sleep(static::$bigTimeout);
                    $curl->__set('requests', []);
                    static::$blocks = [];
                    $blockNum = 0;
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
            
            static::controlStopping();
            
            switch($reqType) {
                case 'list':
                    echo 'Parse list'.PHP_EOL;
                    $properties = static::filterContent($response, 'a.property-grid-table');
                    $cntProps = $properties->count();
                    
                    echo 'cntProps:'.$cntProps.PHP_EOL;
                    if($cntProps > 0) {
                        $cntNew = 0;
                        $page = $request->params['page'];
                        $find_loc = $request->params['find_loc'];
                        for($n = 0; $n < $cntProps; $n++) {
                            $node = $properties->eq($n);
                            $href = $node->attr('href');
                            if(empty($href)) continue;
                            
                            $link = static::$url.$href;
                            if(!$link || strlen($link) < 10) continue;
                            
                            $log = ['method' => 'get', 'url' => $link];
                            if(static::isParsed($log, 2)) {
                                static::$blocks[$reqBlock]['parsed']++;
                                echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
                            } else {
                                $cntNew++;
                                
                                $props = [];
                                $tableTr = $node->filter('table.property-details-table > tr');
                                $tableTrCount = $tableTr->count();
                                if ($tableTrCount) {
                                    for ($k = 2;$k<$tableTrCount;$k++) {
                                        $trTd = $tableTr->eq($k)->filter('td');
                                        if ($trTd->count() > 1) {
                                            $name = strtolower(trim($trTd->eq(0)->text()));
                                            $props[$name] = trim($trTd->eq(1)->text());
                                        }
                                    }
                                }
                                
                                $status = $node->filter('h5');
                                if ($status->count()) {
                                    $props['status'] = strtolower(trim($status->eq(0)->text()));
                                }
                                
                                $address = $node->filter('div.property-address');
                                if ($address->count()){
                                    $props['address'] = trim($address->eq(0)->text());
                                }
                                $props = json_encode($props,JSON_UNESCAPED_UNICODE);
                                
                                $curl->get($link, null, null, ['type' => 'property', 'props' => $props, 'log' => $log, 'block' => $reqBlock]);
                            }
                        }
                        if($cntNew <= 15 && (static::$blocks[$reqBlock]['page'] + 1) == $page) {
                            static::setParsedResults(static::$blocks[$reqBlock]['block'], ['page' => $page]);
                            static::$blocks[$reqBlock]['page'] = $page;
                        }
                        
                        $page++;
                        if($cntProps >= 15) {
                            echo 'next page '.$page.PHP_EOL;
                            $link = static::$url.'/search-property/'.$page.'?comtype=ANY&suburb='.$find_loc.'&radius=50&subtype=ANY&auctions=0&floorsizemin=&floorsizemax=&landsizemin=&landsizeunit=m2&landsizemax=';
                            $curl->get($link, null, null, ['type' => 'list', 'page' => $page, 'url' => $link, 'find_loc' => $find_loc, 'block' => $reqBlock]);
                        }
                    }
                    break;
                
                case 'property':
                    $contentSection = static::filterContent($response, 'section.property.details');
                    if($contentSection->count() == 0 && $request->repeat < 3) {
                        $request->repeat++;
                        $curl->add($request);
                        echo '->'.$request->repeat.PHP_EOL;
                        return true;
                    }
    
                    $data = [];
                    
                    $title = $contentSection->filter('h3');
                    if ($title->count()) {
                        $data['title'] = trim($title->eq(0)->text());
                        if(empty($data['title'])) return true;
                    } else {
                        return true;
                    }
                    
                    $data['log'] = $request->params['log'];
    
                    $block = static::$blocks[$reqBlock];
                    $data['country'] = $block['country'];
                    $data['author'] = static::$userId; //raywhite
                    $data['status'] = 1;
                    $data['currency_code'] = 840;
    
                    $props = json_decode($request->params['props'], true);
                    if (empty($props)) {
                        $propTableTr = static::filterContent($response, '#asset-property + table tr'); // get properties
                        if ($propTableTrCount = $propTableTr->count()) {
                            for ($k = 0; $k < $propTableTrCount; $k++) {
                                $trTd = $propTableTr->eq($k)->filter('td');
                                if ($trTd->count() > 1) {
                                    $name = strtolower(trim($trTd->eq(0)->text()));
                                    $props[$name] = $name != 'property type' ? trim($trTd->eq(1)->text()) : $trTd->eq(1)->html();
                                }
                            }
                        }
                    }
                    print_r($props);
    
                    // fill address
                    if (!isset($props['address']) || empty($props['address'])) {
                        $props['address'] = '';
                        $propTitleAddress = static::filterContent($response, '#asset-property .media-body');
                        if ($propTitleAddress->count()) {
                            $propTitleAddressTxt = trim($propTitleAddress->eq(0)->text());
                            $propTitleAddressTxt = explode('at', $propTitleAddressTxt);
            
                            $props['address'] = trim($propTitleAddressTxt[1]);
                        }
                    }
                    $data['address'] = $data['map_address'] = $props['address'];
    
                    $data['property_status'] = 2;
                    $data['price'] = 1;
                    foreach ($props as $prop_name => $prop_val) {
                        $prop_name = strtolower($prop_name);
                        switch ($prop_name) {
                            case 'status':
                                $data['property_status'] = !empty($prop_val) && isset(static::$propStatuses[$prop_val]) ? static::$propStatuses[$prop_val] : 2;
                                break;
                            case 'address':
                                sleep(1);
                                $link = 'https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext='.str_replace(' ', '%20', $prop_val).'&gen=8&apiKey=D1-ZlZfLX3gShNoLBYg54T6UREl5im0nW9ehoTJLdcA&language=en-US';
                                $ch = @file_get_contents($link);
                                if ($ch) {
                                    $output = json_decode($ch);
                                    if ($output && !empty($output->Response)) {
                                        if (isset($output->Response->View)
                                            && !empty($output->Response->View)
                                            && !empty($output->Response->View[0]->Result)
                                        ) {
                                            echo $data['address'].PHP_EOL;
                
                                            $location
                                                = $output->Response->View[0]->Result[0]->Location;
                                            $data['lat'] = $location->DisplayPosition->Latitude;
                                            $data['lng']
                                                = $location->DisplayPosition->Longitude;
                                            $data['state'] = isset($location->Address->State)
                                                ? $location->Address->State : '';
                                            $data['city'] = isset($location->Address->City)
                                                ? $location->Address->City : '';
                                            $data['postal_code']
                                                = isset($location->Address->PostalCode)
                                                ? $location->Address->PostalCode : '';
                                        }
                                    }
                                }
                                break;
                            case 'property type':
                                if (strpos($prop_val, '<br>') !== false) {
                                    $prop_val = explode('<br>', $prop_val);
                                    $prop_val = $prop_val[0];
                                }
                                if (isset(static::$propTypes[$prop_val])) {
                                    $typeSubType
                                        = static::$propTypes[$prop_val];
                                    if (is_array($typeSubType)) {
                                        $data['property_type'] = $typeSubType[0];
                                        $data['property_subtype'] = $typeSubType[1];
                                    } else {
                                        $data['property_type'] = $typeSubType;
                                    }
                                } else {
                                    static::setError('New property type: '.$prop_val, true, static::getUrlParts($request->url));
                                }
                                break;
                            case 'building area':
                                $prop_val = preg_replace('/\s+/','', $prop_val);
                                if (strpos($prop_val, 'sqm') !== false) {
                                    $prop_val = explode('sqm', $prop_val);
                                    $prop_val = $prop_val[0];
                                } else {
                                    $prop_val = explode('m', $prop_val);
                                    $prop_val = $prop_val[0];
                                }
                                $data['property_area'] = floatval($prop_val);
                                $data['property_area_measure'] = 1;
                                break;
                            case 'land area':
                                $prop_val = preg_replace('/\s+/','', $prop_val);
                                if (strpos($prop_val, 'sqm') !== false) {
                                    $prop_val = explode('sqm', $prop_val);
                                    $prop_val = $prop_val[0];
                                } else {
                                    $prop_val = explode('m', $prop_val);
                                    $prop_val = $prop_val[0];
                                }
                                $data['land_area'] = floatval($prop_val);
                                $data['land_area_measure'] = 1;
                                break;
                            case 'price':
                                $data['property_rent_schedule'] = 0;
                                $hasPrice = strpos($prop_val, '$') !== false;
                                if (!$hasPrice) {
                                    $data['price'] = 1;
                                } else {
                                    $price = strpos($prop_val, ' ') !== false ? explode(' ', $prop_val) : $prop_val;
                                    echo 'Prop price: ';
                                    print_r($price);
                                    if (is_array($price)) {
                                        if ($hasPrice == 0) {
                                            $priceTmp = array_shift($price);
                                            $data['price_after'] = trim(implode(' ', $price));
                                            $price = $priceTmp;
                                            foreach (static::$propRentSchedule as $label => $schedule) {
                                                if (stripos($data['price_after'], $label)) {
                                                    $data['property_rent_schedule'] = $schedule;
                                                    break;
                                                }
                                            }
                                        } else {
                                            $priceTmp = 1;
                                            $data['price_after'] = $data['price_before'] = '';
                                            foreach ($price as $item) {
                                                if (strpos($item, '$') !== false) {
                                                    $priceTmp = $item;
                                                } else {
                                                    if (stripos($prop_val, $item) < stripos($prop_val, '$')) {
                                                        $data['price_before'] .= $item.' ';
                                                    } else {
                                                        $data['price_after'] .= $item.' ';
                                                    }
                                                }
                                            }
                                            $price = $priceTmp;
                                            
                                            foreach (static::$propRentSchedule as $label => $schedule) {
                                                if (stripos($data['price_before'], $label) || stripos($data['price_after'], $label)) {
                                                    $data['property_rent_schedule'] = $schedule;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    $price = str_replace(array('$',' ',','), '', $price);
                                    $data['price'] = floatval($price);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    
                    // fill description
                    $content = $contentSection->filter('p');
                    if ($content->count()) {
                        $contents = $content->each(function ($node, $i) {
                            return $node->text();
                        });
                        !is_array($contents) && $contents = [$contents];
                        $data['descriptions']['en'] = implode('<br>',$contents);
                    }
                    
                    // get images
                    $contentImages = static::filterContent($response, '#property-photos-full .gallery-photo > a');
                    $data['cnt_images'] = $imagesCnt = $contentImages->count();
                    $data['images'] = [];
                    if($imagesCnt > 0) {
                        static::$propertyIdent++;
                        $data['leer_images'] = 0;
                        for($n = 0; $n < $imagesCnt; $n++) {
                            static::$imageIdent++;
                            $link = $contentImages->eq($n)->attr('href');
                            if(!$link || strlen($link) < 10) continue;
                            
                            $link = 'http:' . str_replace(' ', '%20', $link);
                            $data['images'][static::$imageIdent] = [];
                            
                            $curl->get($link, null, null, ['type' => 'image', 'prop_ident' => static::$propertyIdent, 'image_ident' => static::$imageIdent, 'block' => $reqBlock]);
                        }
                    }
                    echo 'images='.$data['cnt_images'].PHP_EOL;
    
                    if(!isset($data['cnt_images']) || empty($data['cnt_images'])) {
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
            $data['price'] = (!isset($data['price']) || empty($data['price'])) ? 1 : $data['price'];
            
            static::saveNewProperty($data, $log);
            
            static::$parsed++;
            static::$blocks[$reqBlock]['parsed']++;
            echo ' parsed='.(static::$blocks[$reqBlock]['parsed']).'|'.static::$parsed.PHP_EOL;
            if(!empty(static::$parseLimit) && static::$parsed >= static::$parseLimit) return false;
            
            if(!empty($ident)) {
                unset(static::$properties[$ident]);
            }
            
            if(static::$parsed > 4000) {
                Setting::setValue('parsers', 'raywhitecomm_proxies', json_encode(static::$curl->getAliveProxies()));
                static::createNewJob();
            }
            
            return true;
        }
    }

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
    
    class RaywhiteParser extends BaseParser
    {
        public static $parserParams = ['locations'];
        public static $userName = '';
        public static $userEmail = 'info@medicaleer.com';
        public static $imageName = 'medicaleer_rw_';
        public static $parseLimit = 0;
        public static $parseBlockLimit = 1000;
        public static $parsedBlocks = 0;
        public static $parsedUsers = 0;
        public static $hashFields = ['url'];
        public static $searchUrl = '';
        
        public static $location = null;
        public static $blocks = [];
        public static $maxBlocks = 1;
        
        public static $propStatuses = ['for Rent' => 1, 'for Sale' => 2, 'for Auction' => 2];
        public static $propTypes = [
            'Apartment' => 1, 'Studio' => 1, 'Flat' => 1, 'Unit' => 1, 'Serviced Apartment' => 1,
            'House' => 2, 'Villa' => 2, 'Block of Units' => 2, 'Residential building' => 2, 'Residential Property' => 2, 'Bungalow' => 2, 'Terrace' => 2, 'Alpine' => 2, 'Semi-Detached' => 2,
            'Rental Property' => 2,
            'Commercial' => 6,
            'Business office' => [6, 1],
            'Industrial building' => [6, 4], 'Warehouse' => [6,4],
            'Farm' => [6, 9],
            'Garage parking' => [6, 10],
            'Retirement' => 7,
            'Townhouse' => 9,
            'Land' => 11, 'Vacant Land' => 11, 'Acreage' => 11, 'Acreage/Semi-Rural' => 11,
            'Other' => 15,
            ];
        
        public static $propRentSchedule = ['Daily' => 1, 'Weekly' => 2, 'Monthly' => 3, 'Yearly' => 4];
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
        
        public static $userId = 2743291; // raywhite
        
        public static $iso2 = '';
        private static $start = LARAVEL_START;
        
        public static function run(Parser $parser)
        {
            $url = $parser->url;
            static::setUrl($url);
            $model = $parser->model;
            $params = static::getParams($model);
    
            static::deleteProperties($params); return true;
            
            static::prepareTempDir('rw_temp');
            static::$imageName .= date('YmdHis').'_';
            
            $curl = new RollingCurl([$model . 'Parser', 'parse']);
            $curl->setReferer('https://www.raywhite.com/');
            $curl->setAuth('Selssaltovsk1:V6w0OcI');
            $curl->setHostIp(gethostbyname('medicaleer.com'));
            $curl->setUserAgents([
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.71",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.88",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0",
                "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko"]);
    
            $curl->setProxies(static::getProxyList(), 'http', 'https://www.raywhite.com/', 'raywhite', 700);
            static::setCurl($curl);
            $countries = Country::getCoyntriesForSelect();
            
            static::$parsed = 0;
            $cntBlocks = 0;
            $defResult = ['done' => 0, 'url' => null, 'page' => 0];
            
            $rwTypes = ['buy', 'rent'];
            
            foreach($params['locations'] as $location) {
                $city = $location->city;
                $state = $location->state;
                $country = trim($location->country);
                $postcode = $location->postcode;
                
                $find_loc = urlencode($city.', '.(empty($state) ? '' : $state.' ').$postcode);
                $block_loc = $city.','.$state.','.$postcode;
                
                foreach ($rwTypes as $rwType) {
                    $blockNum = 0;
    
                    $block = $rwType.'|'.$block_loc;
    
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
                        static::$blocks[$blockNum] = ['parsed' => 0, 'block' => $block, 'rw_type' => $rwType, 'city' => $city, 'state' => $state, 'country' => $countryId, 'postcode' => $postcode, 'countryName' => $country, 'page' => 0, 'new' => true];
        
                        //https://www.raywhite.com/rent/?type=REN&_se=rent&budgetmax=&budgetdisplay=&subtype=ANY&suburb=Brisbane%2C+QLD+4000&radius=0&radius=10&bedroom=any&bathroom=any&garage=any&budgetmin=&_s=rent
                        //https://www.raywhite.com/buy/?type=SAL&_se=buy&budgetmax=&budgetdisplay=&subtype=ANY&suburb=Brisbane%2C+QLD+4000&radius=0&radius=10&bedroom=any&bathroom=any&garage=any&budgetmin=&_s=buy
                        $tmpRwType = $rwType == 'buy' ? 'SAL' : 'REN';
                        if(is_null($parsedResult['url'])) {
                            $link = $url.'/'.$rwType.'/?type='.$tmpRwType.'&_se='.$rwType.'&budgetmax=&budgetdisplay=&subtype=ANY&suburb='.$find_loc.'&radius=0&radius=10&bedroom=any&bathroom=any&garage=any&budgetmin=&_s='.$rwType;
                            $curl->get($link, null, null, ['type' => 'list', 'page' => 1, 'url' => $link, 'rw_type' => $rwType, 'tmp_rw_type' => $tmpRwType, 'find_loc' => $find_loc, 'block' => $blockNum]);
                        } else {
                            $link = $parsedResult['url'];
                            $page = is_null($parsedResult['page']) ? 0 : $parsedResult['page'];
                            if($page > 0) {
                                static::$blocks[$blockNum]['page'] = $page;
                                static::$blocks[$blockNum]['new'] = false;
                            }
                            $page++;
                            if ($page > 1) {
                                $link = $url.'/'.$rwType.'/'.$page.'/?type='.$tmpRwType.'&_se='.$rwType.'&budgetmax=&budgetdisplay=&subtype=ANY&suburb='.$find_loc.'&radius=0&radius=10&bedroom=any&bathroom=any&garage=any&budgetmin=&_s='.$rwType;
                            }
                            $curl->get($link, null, null, ['type' => 'list', 'page' => $page, 'url' => $link, 'rw_type' => $rwType, 'tmp_rw_type' => $tmpRwType, 'find_loc' => $find_loc, 'block' => $blockNum]);
                        }
                        echo '!!!!!'.$link.PHP_EOL;
                    }
                    if($blockNum >= static::$maxBlocks) {
                        static::$properties = [];
                        static::$propertyIdent = 0;
                        static::$users = [];
                        static::$userIdent = 0;
                        echo 'execute'.PHP_EOL;
                        if(!$curl->execute(8)) return true;
                        echo 'Check blocks'.PHP_EOL;
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
                            Setting::setValue('parsers', 'raywhite_proxies', json_encode($curl->getAliveProxies()));
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
            
            //$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            //$header = substr($response, 0, $header_size);
            //$body = substr($response, $header_size);
            static::controlStopping();
            
            switch($reqType) {
                case 'list':
                    echo 'Parse list'.PHP_EOL;
                    $properties = static::filterContent($response, 'article.property-item');
                    $cntProps = $properties->count();
                    
                    echo 'cntProps:'.$cntProps.PHP_EOL;
                    if($cntProps > 0) {
                        $cntNew = 0;
                        $page = $request->params['page'];
                        $rwType = $request->params['rw_type'];
                        $tmpRwType = $request->params['tmp_rw_type'];
                        $find_loc = $request->params['find_loc'];
                        for($n = 0; $n < $cntProps; $n++) {
                            $node = $properties->eq($n);
                            $href = $node->filter('figure a.view-property-details');
                            if($href->count() != 1) continue;
                            
                            $link = static::$url.$href->attr('href');
                            if(!$link || strlen($link) < 10) continue;
                            
                            $log = ['method' => 'get', 'url' => $link];
                            if(static::isParsed($log, 2)) {
                                static::$blocks[$reqBlock]['parsed']++;
                                echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
                            } else {
                                $cntNew++;
                                $curl->get($link, null, null, ['type' => 'property', 'prop_type' => '', 'log' => $log, 'block' => $reqBlock]);
                            }
                        }
                        if($cntNew <= 15 && (static::$blocks[$reqBlock]['page'] + 1) == $page) {
                            static::setParsedResults(static::$blocks[$reqBlock]['block'], ['page' => $page]);
                            static::$blocks[$reqBlock]['page'] = $page;
                        }
                        
                        $page++;
                        if($cntProps >= 15) {
                            echo 'next page '.$page.PHP_EOL;
                            $link = static::$url.'/'.$rwType.'/'.$page.'/?type='.$tmpRwType.'&_se='.$rwType.'&budgetmax=&budgetdisplay=&subtype=ANY&suburb='.$find_loc.'&radius=0&radius=10&bedroom=any&bathroom=any&garage=any&budgetmin=&_s='.$rwType;
                            $curl->get($link, null, null, ['type' => 'list', 'page' => $page, 'url' => $link, 'rw_type' => $rwType, 'tmp_rw_type' => $tmpRwType, 'find_loc' => $find_loc, 'block' => $reqBlock]);
                        }
                    }
                    break;
                
                case 'property':
                    $contentTitle = static::filterContent($response, '#listing-title');
                    if($contentTitle->count() == 0 && $request->repeat < 5) {
                        $request->repeat++;
                        $curl->add($request);
                        echo '->'.$request->repeat.PHP_EOL;
                        return true;
                    }
                    
                    $content = static::filterContent($response, '#listing-desc'); // description
                    $contentLocation = static::filterContent($response, 'div.property-details-location'); // prop type, address, location
                    $contentProperties = static::filterContent($response, 'div.property-overview-header'); // price, bed, bath, garage, area
                    $contentImages = static::filterContent($response, '#property-photos-full .gallery-photo > a');
                    
                    
                    $data = [];
                    if (!$contentTitle->count()) {
                        return true;
                    }
                    $node = $contentTitle->eq(0);
                    $data['title'] = trim($node->text());
                    if(empty($data['title'])) return true;
    
                    $data['log'] = $request->params['log'];
    
                    $data['author'] = static::$userId; //raywhite
                    $data['status'] = 1;
                    $data['currency_code'] = 840;
                    $data['price'] = 1;
                    
                    if ($content->count()) {
                        $node = $content->eq(0);
                        $data['descriptions']['en'] = $node->html();
                    }
                    
                    if ($contentProperties->count()) {
                        $node = $contentProperties->eq(0);
                        
                        $price_str = $price = static::getNodeText($node, '.price', false);
                        if (!is_null($price)) {
                            $data['property_rent_schedule'] = 0;
                            $hasPrice = strpos($price, '$') !== false;
                            if (!$hasPrice) {
                                $data['price'] = 1;
                            } else {
                                if (strpos($price, '-') !== false) {
                                    $price = explode('-', $price);
                                    $data['price'] = floatval(str_replace(array('$',' ',','), '', $price[0]));
                                    $data['price_second'] = floatval(str_replace(array('$',' ',','), '', $price[1]));
                                } else {
                                    $price = strpos($price, ' ') !== false ? explode(' ', $price) : $price;
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
                                            $data['price_before'] = '';
                                            $data['price_after'] = $data['price_before'] = '';
                                            foreach ($price as $item) {
                                                if (strpos($item, '$') !== false) {
                                                    $priceTmp = $item;
                                                } else {
                                                    if (stripos($price_str, $item) < stripos($price_str, '$')) {
                                                        $data['price_before'] .= $item.' ';
                                                    } else {
                                                        $data['price_after'] .= $item.' ';
                                                    }
                                                }
                                            }
                                            $price = $priceTmp;
                                        }
                                    }
                                    $price = str_replace(array('$',' ',','), '', $price);
                                    $data['price'] = floatval($price);
                                }
                            }
                        }
    
                        $beds = static::getNodeText($node, '.bed', false);
                        if (!is_null($beds)) {
                            $data['bedrooms'] = intval(str_replace(array('Beds',' '), '', $beds));
                        }
    
                        $baths = static::getNodeText($node, '.bath', false);
                        if (!is_null($baths)) {
                            $data['bathrooms'] = intval(str_replace(array('Baths',' '), '', $baths));
                        }
    
                        $cars = static::getNodeText($node, '.car', false);
                        if (!is_null($cars)) {
                            $data['garage'] = intval(str_replace(array('Parking',' '), '', $cars));
                        }
                        
                        $listProps = $node->filter('.listing-specs-list li');
                        if ($listPropsCount = $listProps->count()) {
                            for($n=0;$n<$listPropsCount;$n++) {
                                $valText = $listProps->eq($n)->text();
                                $value = static::getNodeText($listProps->eq($n),'.value', false);
                                $value = str_replace([' ', 'acres','sqm'],'', $value);
                                $hasDelim = strpos($value, '/') !== false;
                                if (stripos($valText, 'Building area') !== false) {
                                    $data['property_area'] = floatval($hasDelim ? explode('/', $value)[1] : $value);
                                    $data['property_area_measure'] = 1;
                                } elseif(stripos($valText, 'Land area') !== false) {
                                    $data['land_area'] = floatval($hasDelim ? explode('/', $value)[1] : $value);
                                    $data['land_area_measure'] = 1;
                                }
                            }
                        }
                        
                    }
                    
                    if ($contentLocation->count()) {
                        $node = $contentLocation->eq(0);
    
                        $propType = static::getNodeText($node, '.property-type', false);
                        if (!is_null($propType)) {
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
                                static::setError('New property type: '
                                    .$propType, true,
                                    static::getUrlParts($request->url));
                            }
                        } else {
                            static::setError('Empty property type: '
                                .$propType, true,
                                static::getUrlParts($request->url));
                        }
    
                        $status = static::getNodeText($node,'.property-sale-type', false);
                        $data['property_status'] = !is_null($status) && isset(static::$propStatuses[$status]) ? static::$propStatuses[$status] : 2;
    
                        $addresSpan = $node->filter('.property-micro-address span');
                        if ($addressCount = $addresSpan->count()) {
                            $address = [];
                            for($n=0;$n<$addressCount;$n++) {
                                $value = trim($addresSpan->eq($n)->text());
                                $itemprop = $addresSpan->eq($n)->attr('itemprop');
                                switch ($itemprop) {
                                    case 'addressLocality':
                                        $address[] = $data['city'] = $value;
                                        break;
                                    case 'addressRegion':
                                        $address[] = $data['state'] = $value;
                                        break;
                                    case 'postalCode':
                                        $address[] = $data['postal_code'] = intval($value);
                                        break;
                                    default:
                                        $address[] = $value;
                                        break;
                                }
                            }
    
                            $data['address'] = implode(', ', $address);
                            $data['map_address'] = $data['address'];
                        }
    
                        $latLonSpan = $node->filter('.property-micro-geodata span');
                        if ($latLonCount = $latLonSpan->count()) {
                            for($n=0;$n<$latLonCount;$n++) {
                                $value = trim($latLonSpan->eq($n)->text());
                                $itemprop = $latLonSpan->eq($n)->attr('itemprop');
                                switch ($itemprop) {
                                    case 'latitude':
                                        $data['lat'] = $value;
                                        break;
                                    case 'longitude':
                                        $data['lng'] = $value;
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                    
                    $block = static::$blocks[$reqBlock];
                    $data['country'] = $block['country'];
                    
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
                Setting::setValue('parsers', 'raywhite_proxies', json_encode(static::$curl->getAliveProxies()));
                static::createNewJob();
            }
            
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
                Setting::setValue('parsers', 'raywhite_last', $last);
            } while ($round < $roundMax && $cntProperties > 0);
            echo '$last='.$last.PHP_EOL;
            echo '$all='.$all.PHP_EOL;
            echo '$cnt='.$cnt.PHP_EOL;
            echo 'END delete TIME :: '.(microtime(true) - static::$start).PHP_EOL;
        
            return true;
        }
    }

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
    
    class RealogyC21Parser extends BaseParser
    {
        public static $parserParams = ['skip'];
        public static $userName = '';
        public static $userEmail = 'info@medicaleer';
        public static $imageName = 'medicaleer_realogyc21_';
        public static $parseLimit = 0;
        public static $parseBlockLimit = 1000;
        public static $parsedBlocks = 0;
        public static $parsedUsers = 0;
        public static $hashFields = ['url'];
        public static $searchUrl = '';
        private static $geocoderApiKey = 'Al-pQcxe5clM9f_03bItLzPNrCLLC3Wg5L5j1ZeWVaY';
        private static $start = LARAVEL_START;
        
        public static $location = null;
        public static $blocks = [];
        public static $maxBlocks = 1;
        public static $propStatuses = ['ForRent' => 1, 'ForSale' => 2, 'ForLeaseCommercial' => 1, 'ForSaleCommercial' => 2];
        public static $propTypes = [
            'Apartment' => 1,
            'House' => 2,
            'Room' => 3,
            'Temporary' => 4,
            'Flatmate' => 5,
            'Commercial' => 6,
            'Offices' => [6, 1],
            'Shared Workspaces' => [6,2],
            'Retail' => [6, 3],
            'Industrial' => [6, 4], 'Warehouse' => [6,4], 'Industrial/Warehouse' => [6,4],
            'Showrooms/Bulky Goods' => [6,5], 'Showrooms' => [6,5], 'Bulky Goods' => [6,5], 'OfficesShowrooms/Bulky Goods' => [6,5],
            'Land/Development' => [6,6],
            'Hotel' => [6, 7], 'Hotel/Leisure' => [6,7],
            'Medical/Consulting' => [6,8], 'Medical' => [6,8], 'Consulting' => [6,8],
            'Commercial Farming' => [6, 9],
            'Garage parking' => [6, 10], 'Tourism' => [6,10],
            'Retirement Living' => 7,
            'Vacation Home' => 8,
            'Townhouse' => 9,
            'Project Home' => 10,
            'Land' => 11,
            'Island' => 12,
            'Insolvency Property' => 13,
            'Vineyard' => 14,
            'Other' => 15,
            
            'Residential' => 2,
            'Lots & Land' => 11,
            'Lots & Land-Other' => 11,
            'Land -Single Family Acreage' => 11,
            'Farm and Agriculture' => [6, 9],
            ];
        
        public static $propRentSchedule = ['Daily' => 1, 'Weekly' => 2, 'Monthly' => 3, 'Yearly' => 4];
        public static $properties = [];
        public static $propertyIdent = 0;
        public static $users = [];
        public static $userIdent = 0;
        public static $imageIdent = 0;
        public static $imagesErrors = 0;
        public static $imagesErrorLimit = 10000;
        public static $leerImages = [
            '52ce81df817053b98c62379d93a3e6bb', '6b243f62d70373eeade2f2cb5866c678',
            '95521009dfa769f353ac6d07e583dee6', 'bc09560da3ad29f7b3d9b7d04169b7c0',
            '0bbd03e6ab8dae1f72593108207f2aab', '6b1b95297363168987eab153d8b0ef6a',
            '0e74e5a5f11a66beaf2bb6eec5cd48df', 'e3ac255e38abe0089afa0fbcafe3a5b3',
            '9df30da6015cd9e4351122619e6e9464', '86c5f0dbde6da36fb496592f767972c2'];
        public static $imageTypes = [1 => '.gif', 2 => '.jpg', 3 => '.png'];
        
        public static $windowSize = 10;
        
        public static $iso2 = '';
        public static $brands = ['C21'];
        private static $tokenUrl = 'https://realogy.okta.com/oauth2/aus7i8b1taFyPOEGc1t7/v1/token';
        private static $scope = 'https://btt.realogyfg.com/searchapi';
        private static $grantType = 'client_credentials';
        private static $tokenApi = 'eyJraWQiOiJSOTUtdlFydWRISUoyVW5ycnc4d1pUN2RpeWlzVEdVN1pDT3BuelBaUEc0IiwiYWxnIjoiUlMyNTYifQ.eyJ2ZXIiOjEsImp0aSI6IkFULjRmX1BSYVhLdENNMzdOTmFIQkF5WG51aGVpYS1VN0FTZVMxeHRmc1hCa0kiLCJpc3MiOiJodHRwczovL3JlYWxvZ3kub2t0YS5jb20vb2F1dGgyL2F1czdpOGIxdGFGeVBPRUdjMXQ3IiwiYXVkIjoiaHR0cHM6Ly9idHQucmVhbG9neWZnLmNvbSIsImlhdCI6MTU4NDI5MjE0OSwiZXhwIjoxNTg0Mjk1NzQ5LCJjaWQiOiIwb2FkbGJlOHRlSHNwTEpibDF0NyIsInNjcCI6WyJodHRwczovL2J0dC5yZWFsb2d5ZmcuY29tL3NlYXJjaGFwaSJdLCJzdWIiOiIwb2FkbGJlOHRlSHNwTEpibDF0NyIsInBoeXNpY2FsRGVsaXZlcnlPZmZpY2VOYW1lIjoiIiwiZXh0ZW5zaW9uQXR0cmlidXRlMiI6IiIsIm1haWwiOiIiLCJzQU1BY2NvdW50TmFtZSI6IiIsIlVzZXJTZWN1cml0eUdyb3VwcyI6IiIsImRlc2NyaXB0aW9uIjoiIiwiZW1wbG95ZWVJRCI6IiIsInVzZXJpZCI6IiIsImV4dGVuc2lvbkF0dHJpYnV0ZTkiOiIiLCJvcmdpZCI6IjAwbzNtNmFsUnpaVFlwUkdKMXQ1IiwiZW1wbG95ZWVUeXBlIjoiIiwiZXh0ZW5zaW9uQXR0cmlidXRlMTAiOiIifQ.UJ1FnV8NVvT7MQDYD8BfZ37b3NACS--cIBjYYkgcH7VHqa3yUlkMEjaqUMPUq1oTkwhxQbW43yph2B0PA2ck-X7sVNI3wlqSOcvnCmyZxehqMx1N_xc3QS4fdetrKyPivbbS5I349mkXaUY5C_WJirdSITeyxo3KQXf2KuuxhUi69xXtjJVgb9AUh_9gvsNHvyteOcyqZfOmVIA8fU9wSei9b9jqZvma90HosEaTllhKIP9r7XDLMn0OPVqq9EUXu9TTnk8LfX920RgumqT3tlsced_NI3s3YFH8UnP32-ZnQdBUkouVgo1lf_oEKEAt5Uz0wvXIxQr1E5KhNpRg1A';
        private static $clientId = '0oadlbe8teHspLJbl1t7';
        private static $clientSecret = 'M9CGzWHHvSNu29h8-21cFY9GiekntuGw0yiyRm8I';
        private static $keyApi = 'kJB7gnrSmSfsRPMfa248RVduey5IntJw';
        private static $tokenExpires = 0;
        private static $tokenType = 'Bearer';
        
        public static function controlTokenApi()
        {
            if ((time() + 300) > static::$tokenExpires) {
                echo 'Start tokenize TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                $send = [
                    'grant_type' => static::$grantType,
                    'scope' => static::$scope,
                    'client_id' => static::$clientId,
                    'client_secret' => static::$clientSecret
                ];
                $command = 'curl -X POST "'.static::$tokenUrl.'"'
                    .' -H "Content-Type: application/x-www-form-urlencoded"'
                    .' -d "'.http_build_query($send).'"';
                exec($command, $data);
                $data = isset($data[0]) ? json_decode($data[0], true) : [];
                if (!empty($data) && isset($data['access_token'])) {
                    static::$tokenType = $data['token_type'];
                    static::$tokenExpires = time() + $data['expires_in'];
                    static::$tokenApi = $data['access_token'];
                } else {
                    static::setError('Token error: '.$data['errorSummary'], true, $data);
                }
                echo 'End tokenize TIME :: '.(microtime(true) - static::$start).PHP_EOL;
            }
        }
        
        public static function run(Parser $parser)
        {
            $url = $parser->url;
            static::setUrl($url);
            $model = $parser->model;
            $params = static::getParams($model);
    
            //static::compressImages($params); return true;
    
            static::prepareTempDir('realogyc21_temp');
            static::$imageName .= date('YmdHis').'_';
            static::controlTokenApi();
            
            $curl = new RollingCurl([$model . 'Parser', 'parse']);
            $curl->setReferer('https://api.realogy.com');
            $curl->setHostIp(gethostbyname('medicaleer.com'));
            $curl->__set('headers', ['Content-Type: application/json','Authorization: Bearer '.static::$tokenApi, 'apikey: '.static::$keyApi]);
            $curl->setUserAgents([
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.71",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36 OPR/63.0.3368.88",
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0",
                "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko"]);
    
            static::setCurl($curl);
            static::$parsed = 0;
            $defResult = ['done' => 0, 'url' => null, 'page' => 0];
            $limit = 50;
            $end = $params['skip'] + 1000000;
            
            echo 'Start parse TIME :: '.(microtime(true) - static::$start).PHP_EOL;
            foreach (static::$brands as $brand) {
                for($k = $params['skip']; $k <= $end; $k += $limit) {
                    $blockNum = 0;
                    $block = 'RealogyDesc'.$brand.','.$k;
                    $parsedResult = static::getParsedResults($block);
                    echo $block.PHP_EOL;
                    //static::compressImages($params);
                    if(!isset($parsedResult['done'])) $parsedResult = $defResult;
                    if($parsedResult['done'] == 0) {
                        print_r($parsedResult);
            
                        $blockNum++;
                        static::$blocks[$blockNum] = ['parsed' => 0, 'block' => $block, 'skip' => $k, 'new' => true];
            
                        if(is_null($parsedResult['url'])) {
                            $link = $url.'/listings/?sortBy=listedOn%20desc&select=listingId&brandCodes='.$brand.'&top='.$limit;
                            $link = $k ? $link.'&skip='.$k : $link;
                            $curl->get($link, null, null, ['type' => 'list', 'url' => $link, 'block' => $blockNum]);
                        } else {
                            $link = $parsedResult['url'];
                            $curl->get($link, null, null, ['type' => 'list', 'url' => $link, 'block' => $blockNum]);
                        }
                        echo '!!!!!'.$link.PHP_EOL;
                    }
                    if($blockNum >= static::$maxBlocks) {
                        Setting::setValue('parsers', 'realogyc21_skip', $k);
                        static::$properties = [];
                        static::$propertyIdent = 0;
                        static::$users = [];
                        static::$userIdent = 0;
                        echo 'Start execute. TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                        gc_collect_cycles();
                        if(!$curl->execute(static::$windowSize, false)) return true;
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
                
                            static::setParsedResults($data['block'], ['done' => ($data['new'] ? 1 : 0), 'parsed' => $data['parsed']]);
                            static::$parsedBlocks++;
                        }
                        echo ' parsedBlocks='.static::$parsedBlocks.PHP_EOL;
                        if(!empty(static::$parseBlockLimit) && static::$parsedBlocks >= static::$parseBlockLimit) return true;
            
                        static::controlStopping();
                        static::controlTokenApi();
                        if(static::$parsed > 50) {
                            //static::createNewJob();
                        }
            
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
            $proxy = '';//$request->options[CURLOPT_PROXY];
            echo '---------------------'.PHP_EOL;
            echo $request->url.' PROXY:'.$proxy;
            $curl = static::$curl;
            
            echo count($curl->requests).'|'.count($curl->requestMap).PHP_EOL;
            
            $reqType = $request->params['type'];
            $reqBlock = $request->params['block'];
            
            static::controlStopping();
    
            $responseJson = json_decode($response, true);
            
            switch($reqType) {
                case 'list':
                    static::controlTokenApi();
                    echo 'Parse list TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                    if (!isset($responseJson['results'])) {
                        static::setError('Empty results: '.$request->url, true,
                            static::getUrlParts($request->url));
                    } elseif (empty($responseJson['results'])) {
                        return false;
                    }
                    $listings = $responseJson['results'];
                    $cntProps = count($listings);
                    
                    echo 'cntProps:'.count($listings).PHP_EOL;
                    if($cntProps > 0) {
                        $cntNew = 0;
                        foreach($listings as $n => $listing) {
                            if(empty($listing)) continue;
                            $link = $listing['link'];
                            if(!$link || strlen($link) < 10) continue;
                            
                            $log = ['method' => 'get', 'url' => $link, 'listing_id' => $listing['listingSummary']['listingId']];
                            if(static::isParsed($log, 2, 6)) {
                                static::$blocks[$reqBlock]['parsed']++;
                                echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
                            } elseif(static::isParsed($log, 2)) {
                                static::$blocks[$reqBlock]['parsed']++;
                                echo 'isParsed->'.(static::$blocks[$reqBlock]['parsed']).PHP_EOL;
                            } else {
                                $cntNew++;
                                $curl->get($link, null, null, ['type' => 'property', 'log' => $log, 'block' => $reqBlock]);
                            }
                        }
                    }
                    echo 'End Parse list TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                    break;
                
                case 'property':
                    echo 'Parse property. TIME :: '.(microtime(true) - static::$start).PHP_EOL;
    
                    static::controlTokenApi();
                    if (!isset($responseJson['listingSummary']) && $request->repeat < 3) {
                        $request->repeat++;
                        $curl->add($request);
                        echo '->'.$request->repeat.PHP_EOL;
                        return true;
                    }
                    
                    $summary = isset($responseJson['listingSummary']) ? $responseJson['listingSummary'] : [];
                    if (empty($summary)) {
                        static::setError('Empty property result: '.$request->url, true,
                            static::getUrlParts($request->url));
                    }
                    
                    $data = [];
                    
                    if (isset($summary['propertyName'])) {
                        $data['title'] = trim($summary['propertyName']);
                    }
                    
                    $data['log'] = $request->params['log'];
    
                    $address = isset($summary['propertyAddress']) ? $summary['propertyAddress'] : [];
                    if (empty($address)) {
                        return true;
                    }
    
                    if(empty($data['title'])) {
                        if (isset($address['formattedAddress'])) {
                            $data['title'] = $address['formattedAddress'];
                        } else {
                            return true;
                        }
                    }
    
                    static::$propertyIdent++;
    
                    // fill address
                    $data['map_address'] = $data['address'] = '';
                    $addressAr = [];
                    if (isset($address['formattedAddress']) && !empty($address['formattedAddress'])) {
                        $data['map_address'] = $data['address'] = trim($address['formattedAddress']);
                    }
                    $addressAr[] = $data['city'] = isset($address['city']) ? $address['city'] : '';
                    $addressAr[] = $data['postal_code'] = isset($address['postalCode']) ? $address['postalCode'] : '';
                    if (isset($address['stateProvinceCode'])) {
                        $data['state'] = $address['stateProvinceCode'];
                    } elseif (isset($address['state'])) {
                        $data['state'] = $address['state'];
                    } elseif (isset($address['stateProvince'])) {
                        $data['state'] = $address['stateProvince'];
                    } else {
                        $data['state'] = '';
                    }
                    $addressAr[] = $data['state'];
                    $data['lat'] = isset($address['latitude']) ? $address['latitude'] : '';
                    $data['lng'] = isset($address['longitude']) ? $address['longitude'] : '';
                    $addressAr[] = $data['country'] = isset($address['countryCode']) ? trim($address['countryCode']) : '';
                    $len = strlen($data['country']);
                    $result = [];
                    if ($len == 2) {
                        $result = Country::where('iso2', $data['country'])->get()->toArray();
                    } elseif ($len == 3) {
                        $result = Country::where('iso3', $data['country'])->get()->toArray();
                    } elseif ($len > 0) {
                        //$result = Country::whereRaw('("name" ILIKE '.DB::getPdo()->quote('%'.$address['country'].'%').')')->get()->toArray();
                        $result = Country::where('name', 'ILIKE', DB::getPdo()->quote('%'.$address['country'].'%'))->get()->toArray();
                    }
                    if($result) {
                        $country = array_shift($result);
                        $data['country'] = $country['id'];
                    } else {
                        $data['country'] = '';
                    }
    
                    if (empty($data['address']) && !empty($addressAr)) {
                        $data['map_address'] = $data['address'] = implode(',', $addressAr);
                    }
    
                    $data['needGeocoder'] = false;
                    if (empty($data['lat']) || empty($data['lng']) || empty($data['country'])) {
                        if (!empty($data['address'])) {
                            $data['needGeocoder'] = true;
                            $link = 'https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext='.str_replace(' ', '%20', $data['address']).'&gen=8&apiKey='.static::$geocoderApiKey.'&language=en-US';
                            $curl->get($link, null, null, ['type' => 'geosearch', 'num' => 1, 'prop_ident' => static::$propertyIdent, 'block' => $reqBlock]);
                        } else {
                            return true;
                        }
                    }
                    
                    $data['status'] = $summary['isActive'] ? 1 : 2;
                    $data['currency_code'] = 840; // usd
    
                    $data['property_status'] = isset(static::$propStatuses[$summary['listingType']]) ? static::$propStatuses[$summary['listingType']] : 1;
                    $data['price'] = 1;
                    
                    // property type
                    $type = isset($summary['propertyType']) ? $summary['propertyType'] : '';
                    $category = isset($summary['propertyCategory']) ? $summary['propertyCategory'] : '';
                    if ($category) {
                        if (isset(static::$propTypes[$category])) {
                            if (is_array(static::$propTypes[$category])) {
                                $data['property_type']
                                    = static::$propTypes[$category][0];
                                $data['property_subtype']
                                    = static::$propTypes[$category][1];
                            } else {
                                $data['property_type'] = static::$propTypes[$category];
                            }
                        } else {
                            static::setError('New category property type: '
                                .$category, true,
                                static::getUrlParts($request->url));
                        }
                    }
                    if (!isset($data['property_type']) && $type) {
                        if (isset(static::$propTypes[$type])) {
                            $typeSubType
                                = static::$propTypes[$type];
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
                                .$type, true,
                                static::getUrlParts($request->url));
                        }
                    }
    
                    $data['property_area_measure'] = 2;
                    if (isset($summary['squareFootage'])) {
                        $data['property_area'] = floatval($summary['squareFootage']);
                    } elseif (isset($summary['totalAcres'])) {
                        $data['property_area'] = floatval($summary['totalAcres']) * 43560;
                    } elseif (isset($summary['lotSize'])) {
                        $lotSize = explode(' ', $summary['lotSize']);
                        $lastLotSymbols = end($lotSize);
                        $lastLotSymbols = strtoupper($lastLotSymbols);
                        switch ($lastLotSymbols) {
                            case 'AC':
                                $data['property_area_measure'] = 2;
                                $data['property_area'] = floatval( array_shift($lotSize) * 43560 ); // convert to sf
                                break;
                            default:
                                break;
                        }
                        
                    }
                    
                    $data['bedrooms'] = intval(isset($summary['noOfBedrooms']) ? $summary['noOfBedrooms'] : 0);
                    $data['bathrooms'] = intval(isset($summary['totalBath']) ? $summary['totalBath'] : 0);
                    $data['garage'] = intval(isset($summary['parkingPlaces']) ? $summary['parkingPlaces'] : 0);
                    
                    if (isset($summary['listPrice'])) {
                        if (isset($summary['listPrice']['inUSD'])) {
                            $data['price'] = floatval($summary['listPrice']['inUSD']);
                        } elseif (isset($summary['listPrice']['amount'])) {
                            $data['price'] = floatval($summary['listPrice']['amount']);
                        } else {
                            $data['price'] = 1;
                        }
                    } else {
                        $data['price'] = 1;
                    }
    
                    $data['descriptions'] = [];
                    if (isset($responseJson['remarks']) && !empty($responseJson['remarks'])) {
                        foreach ($responseJson['remarks'] as $remark) {
                            switch ($remark['type']) {
                                case 'Property Description':
                                    $data['descriptions'][$remark['languageCode']]
                                        = trim(isset($remark['htmlRemark']) ? $remark['htmlRemark'] : $remark['remark']);
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                    
                    // get company
                    if (isset($summary['agents'])) {
                        $agent = array_shift($summary['agents']);
                        if (isset($agent['office']) && isset($agent['office']['companyName'])) {
                            $data['log']['brand_code'] = $agent['office']['brandCode'];
                            $companyName = trim($agent['office']['companyName']);
                            
                            $user = User::select('id')
                                ->where([['users.name','=',$companyName]])
                                ->first();
                            
                            if ($user) {
                                $data['author'] = $user->id;
                            } else {
                                $company = [
                                    'role_name' => 'agency',
                                    'role_id' => 3,
                                    'name' => $companyName,
                                    'companyId' => $agent['office']['companyId']
                                ];
    
                                $pos = strpos($companyName, ' ');
                                if ($pos === false) {
                                    $company['first_name'] = $companyName;
                                    $company['last_name'] = ' ';
                                } else {
                                    $company['first_name'] = substr($companyName, 0, $pos);
                                    $company['last_name'] = substr($companyName, $pos + 1);
                                }
                                
                                if (isset($agent['office']['type'])
                                    && stripos($agent['office']['type'], 'Main office') !== false
                                    && isset($agent['office']['officeAddress'])) {
                                    $company['map_address'] = $company['address'] = isset($agent['office']['officeAddress']['formattedAddress'])
                                        ? $agent['office']['officeAddress']['formattedAddress'] : '';
                                    $company['lat'] = isset($agent['office']['officeAddress']['latitude'])
                                        ? $agent['office']['officeAddress']['latitude'] : '';
                                    $company['lng'] = isset($agent['office']['officeAddress']['longitude'])
                                        ? $agent['office']['officeAddress']['longitude'] : '';
                                    $company['city'] = isset($agent['office']['officeAddress']['city'])
                                        ? $agent['office']['officeAddress']['city'] : '';
                                    $len = strlen($agent['office']['officeAddress']['country']);
                                    $resultCountry = [];
                                    if($len == 2) {
                                        $resultCountry = Country::where('iso2', $agent['office']['officeAddress']['country'])->get()->toArray();
                                    } elseif ($len == 3) {
                                        $resultCountry = Country::where('iso3', $agent['office']['officeAddress']['country'])->get()->toArray();
                                    } elseif ($len > 0) {
                                        //$resultCountry = Country::whereRaw('("name" ILIKE ' .DB::getPdo()->quote('%'.$agent['office']['officeAddress']['country'].'%').')')
                                        $resultCountry = Country::where('name', 'ILIKE', DB::getPdo()->quote('%'.$agent['office']['officeAddress']['country'].'%'))
                                            ->get()
                                            ->toArray();
                                    }
                                    if($resultCountry) {
                                        $country = array_shift($resultCountry);
                                        $company['country'] = $country['name'];
                                    }
                                }
                                $data['log']['company'] = json_encode($company);
    
                                $userId = static::saveNewUser($company, $data['log'], []);
                                if ($userId) {
                                    $data['author'] = $userId;
                                }
                            }
                            
                        } else {
                            return true;
                        }
                    } else {
                        return true;
                    }
                    
                    // get images
                    $data['cnt_images'] = $imagesCnt = 0;
                    $data['images'] = [];
                    if (isset($responseJson['media']) && !empty($responseJson['media'])) {
                        $imagesCnt = count($responseJson['media']);
                        if($imagesCnt > 0) {
                            $data['leer_images'] = 0;
                            foreach($responseJson['media'] as $n => $media) {
                                if ($media['format'] != 'Image') {
                                    continue;
                                }
                                static::$imageIdent++;
                                $data['cnt_images']++;
                                $link = strpos($media['url'], 'http') === 0 ? $media['url']
                                    : 'http:'.$media['url'];
                                if(!$link) continue;
                                
                                $data['images'][static::$imageIdent] = [];
            
                                $curl->get($link, null, [CURLOPT_REFERER => 'http://imgs.azureedge.net'], ['type' => 'image', 'prop_ident' => static::$propertyIdent, 'image_ident' => static::$imageIdent, 'block' => $reqBlock]);
                            }
                        }
                    }
                    echo 'images='.$data['cnt_images'].' TIME :: '.(microtime(true) - static::$start).PHP_EOL;;
    
                    if(empty($data['cnt_images']) && !$data['needGeocoder']) {
                        if(!static::saveProperty($data, 0, $reqBlock)) return false;
                    } else {
                        static::$properties[static::$propertyIdent] = $data;
                    }
                    break;
                case 'image':
                    echo 'Parse image TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                    
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
                        if($request->repeat < 2) { //} && !$errorType) {
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
                    
                    if(empty(static::$properties[$propertyIdent]['cnt_images']) && !static::$properties[$propertyIdent]['needGeocoder']) {
                        if(!static::saveProperty(static::$properties[$propertyIdent], $propertyIdent, $reqBlock)) return false;
                    }
                
                    echo 'END Parse image TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                    
                    break;
                case 'geosearch':
                    echo 'Parse geosearch TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                    
                    $propertyIdent = $request->params['prop_ident'];
                    $property = static::$properties[$propertyIdent];
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
                            $location = $output->Response->View[0]->Result[0]->Location;
                            echo 'Location:'.PHP_EOL;
                            print_r($location);
                            $property['lat'] = empty($property['lat']) ? $location->DisplayPosition->Latitude : $property['lat'];
                            $property['lng'] = empty($property['lng']) ? $location->DisplayPosition->Longitude : $property['lng'];
                            $property['state'] = empty($property['state']) && isset($location->Address->State)
                                ? $location->Address->State : $property['state'];
                            $property['city'] = empty($property['city']) && isset($location->Address->City)
                                ? $location->Address->City : $property['city'];
                            $property['postal_code'] = empty($property['postal_code']) && isset($location->Address->PostalCode)
                                ? $location->Address->PostalCode : $property['postal_code'];
            
                            if (empty($property['country']) && $location->Address->Country) {
                                $len = strlen(trim($location->Address->Country));
                                if ($len == 3) {
                                    $property['country'] = Country::getCountryId(trim($location->Address->Country), 'iso3');
                                } elseif ($len == 2) {
                                    $property['country'] = Country::getCountryId(trim($location->Address->Country), 'iso2');
                                } else {
                                    //$res = Country::whereRaw('("name" ILIKE '.DB::getPdo()->quote('%'.$location->Address->Country.'%').')')
                                    $res = Country::where('name', 'ILIKE', DB::getPdo()->quote('%'.$location->Address->Country.'%'))
                                        ->get()
                                        ->toArray();
                                    if (!empty($res)) {
                                        $country = array_shift($res);
                                        $property['country'] = $country['id'];
                                    }
                                }
                            } elseif (empty($property['country'])) {
                                unset(static::$properties[$propertyIdent]);
                                return false;
                            }
    
                            static::$properties[$propertyIdent] = $property;
                            
                            if(empty(static::$properties[$propertyIdent]['cnt_images'])) {
                                if(!static::saveProperty(static::$properties[$propertyIdent], $propertyIdent, $reqBlock)) return false;
                            }
                        }
                    }
    
                    if (!$isAddressed && !empty($property['address']) && $num < 3) {
                        echo 'Geocode->'.$num.' :: '.$property['address'].PHP_EOL;
                        $address = array_map(function($item){
                            return trim($item);
                        },explode(',', $property['address']));
        
                        array_pop($address);
                        $address = implode(' ', $address);
                        $link = 'https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext='.str_replace(' ', '%20', $address).'&gen=8&apiKey='.static::$geocoderApiKey.'&language=en-US';
                        $curl->get($link, null, null, ['type' => 'geosearch', 'num' => $num, 'prop_ident' => $propertyIdent, 'block' => $reqBlock]);
                    }
                    echo 'END Parse geosearch TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                    break;
            }
            
            return true;
        }
        
        public static function saveImage(&$fileName, $data, $info, $minSize = 1000) {
            echo 'Start save image TIME :: '.(microtime(true) - static::$start).PHP_EOL;
            
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
            //if($is[2] != 2) {
                //echo $fileName;
                //print_r($is);
                
                $fileSize = $size / 1024;
                echo '======= Destination filesize: '.$fileSize.PHP_EOL;
                clearstatcache(true, $fileName);
    
                if ($fileSize > 3000) { // more than ~ 3mb
                    Image::make($fileName)->encode('jpg', 65)->save($fileName, 15);
                } elseif ($fileSize > 1000) { // more than ~ 1mb
                    Image::make($fileName)->encode('jpg', 65)->save($fileName, 30);
                } elseif ($fileSize > 500) {
                    Image::make($fileName)->encode('jpg', 65)->save($fileName, 45);
                } elseif ($fileSize > 200) {
                    Image::make($fileName)->encode('jpg', 65)->save($fileName, 60);
                } else {
                    Image::make($fileName)->encode('jpg', 65)->save($fileName);
                }
                
                /*$oldFile = $fileName;
				$fileName = str_replace('.jpg', static::$imageTypes[$is[2]], $oldFile);
				if(!rename($oldFile, $fileName)) return false;*/
            //}
            //elseif (!in_array($is[2], array(1,2,3))) return false
            echo 'End save image TIME :: '.(microtime(true) - static::$start).PHP_EOL;
            return $size;
        }
        
        public static function saveProperty($data, $ident, $reqBlock)
        {
            echo 'Start save poperty TIME :: '.(microtime(true) - static::$start).PHP_EOL;
            echo 'wait=>'.sizeof(static::$properties).' save=>'.$ident.' | ';
            
            $log = $data['log'];
            print_r($log);
            if (empty($data['country'])) {
                echo 'Empty country!!!'.PHP_EOL;
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
            $data['price'] = (!isset($data['price']) || empty($data['price']) || $data['price'] < 1) ? 1 : $data['price'];
//            echo 'Property price before save:'.PHP_EOL;
//            print_r($data['price']);
            static::saveNewProperty($data, $log);
            
            static::$parsed++;
            static::$blocks[$reqBlock]['parsed']++;
            echo ' parsed='.(static::$blocks[$reqBlock]['parsed']).'|'.static::$parsed.PHP_EOL;
            echo 'END save property TIME :: '.(microtime(true) - static::$start).PHP_EOL;
            if(!empty(static::$parseLimit) && static::$parsed >= static::$parseLimit) return false;
    
            if(static::$parsed > 20) {
                static::createNewJob();
            }
            
            if(!empty($ident)) {
                unset(static::$properties[$ident]);
            }
            
            return true;
        }
    
        public static function compressImages($params)
        {
            $round = 1;
            $all = 0;
            $last = isset($params['last']) ? $params['last'] : 0;
            $limit = 100;
            $roundMax = 1;
            $cnt = 0;
            $uploadsPath = Upload::getUploadsPath();
            do {
                echo '------COMPRESS IMAGES '.$round.'------'.PHP_EOL;
                echo 'Start compress TIME :: '.(microtime(true) - static::$start).PHP_EOL;
                $uploads = ParserLog::select('id','entity_id', 'message as destination')
                    ->where([['parser_id', 6], ['entity_type', 3], ['result', 1]])
                    ->where('id', '>', $last)
                    ->orderBy('id', 'asc')
                    ->limit($limit)
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
                    $fileName = $uploadsPath . '/' . $upload['destination'];
                
                    if(file_exists($fileName)) {
                        $fileSize = filesize($fileName) / 1024;
                    
                        if ($fileSize > 200) {
                            echo '======= Destination file: '.$fileName.PHP_EOL;
                            echo '======= Destination filesize: '.$fileSize.PHP_EOL;
                        
                            if ($fileSize > 3000) { // more than ~ 3mb
                                Image::make($fileName)->save($fileName, 15);
                            } elseif ($fileSize > 1000) { // more than ~ 1mb
                                Image::make($fileName)->save($fileName, 30);
                            } elseif ($fileSize > 500) {
                                Image::make($fileName)->save($fileName, 45);
                            } elseif ($fileSize > 200) {
                                Image::make($fileName)->save($fileName, 60);
                            }
                        
                        }
                    }
                    $last = $id;
                }
                $round++;
                $all += $cntUploads;
                Setting::setValue('parsers', 'realogy_last', $last);
            } while ($round < $roundMax && $cntUploads > 0);
            echo '$last='.$last.PHP_EOL;
            echo '$all='.$all.PHP_EOL;
            echo '$cnt='.$cnt.PHP_EOL;
            echo 'END compress TIME :: '.(microtime(true) - static::$start).PHP_EOL;
        
            return true;
        }
    
    }

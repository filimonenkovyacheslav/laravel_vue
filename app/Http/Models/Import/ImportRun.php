<?php

namespace App\Http\Models\Import;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use CustomLaravelLocalization;
use Validator;
use BaseModel;
use DB;
use ImportLink;
use ImportLog;
use Property;
use PropertyLang;
use FeatureProperty;
use Feature;
use Upload;
use UploadProperty;
use User;
use Measure;
use Currency;
use Country;

class ImportRun extends Model
{
	public $timestamps = false;
	protected $table = 'import_runs';

	public $fillable = [
		'link_id', 'run_date', 'run_time', 'ended', 'cnt_inserted', 'cnt_updated', 'cnt_deleted', 'files_added', 'files_deleted', 'cnt_errors'
	];
	public static function getStatuses() {
		return [
			0 => __('running'),
			1 => __('ok'),
			2 => __('error'),
		];
	}

	public static $defaultLang = null;
	public static $validationRules = [];

	public static $importPath = '';
	public static $uploadsPath = '';
	private static $uploadTypes = ['image' => 1, 'video' => 2];

	public static function setImportPath($dir = '')
	{
		$dir = public_path(empty($dir) ? '/import' : '');
		if(!file_exists($dir)) {
			mkdir($dir, 0777);
		}
		static::$importPath = $dir;
		static::$uploadsPath = Upload::getUploadsPath();
	}

	public static function setDefaultLang()
	{
		static::$defaultLang = config('app')['default_lang'];
	}

	public static function setValidationRules($forImport = true)
	{
		$rules = [
			'convert_fields' => ['currency_code', 'country', 'lang'],
			'field_aliases' => [
				'price_second' => 'priceSecond',
				'currency_code' => 'currencyCode',
				'price_before' => 'priceBefore',
				'price_after' => 'priceAfter',
				'price_hidden' => 'priceHidden',
				'property_type' => 'propertyType',
				'property_subtype' => 'propertySubtype',
				'property_status' => 'propertyStatus',
				'property_rent_schedule' => 'propertyRentSchedule',
				'postal_code' => 'postalCode',
				'map_address' => 'mapAddress',
				'property_area' => 'propertyArea',
				'property_area_measure' => 'propertyAreaMeasure',
				'land_area' => 'landArea',
				'land_area_measure' => 'landAreaMeasure',
				'garage_area' => 'garageArea',
				'garage_area_measure' => 'garageAreaMeasure',
				'year_built' => 'yearBuilt'
			],
			'lang_fields' => [
				'title' => 'required|string|max:255',
				'address' => 'string|max:255|nullable',
				'description' => 'string|nullable'
			],
			'allowed_fields' => [
				'id' => 'int|min:1',
				'label' => 'int|nullable',
				'price' => 'required|numeric|min:1',
				'price_second' => 'numeric|min:1|nullable',
				'currency_code' => 'required|int',
				'price_before' => 'numeric|string|max:191|nullable',
				'price_after' => 'numeric|string|max:191|nullable',
				'price_hidden' => 'boolean|nullable',
				'property_type' => 'int|nullable',
				'property_subtype' => 'int|nullable',
				'property_status' => 'int|nullable',
				'property_rent_schedule' => 'int|nullable',
				'postal_code' => 'string|max:255|nullable',
				'country' => 'int|nullable',
				'state' => 'string|max:255|nullable',
				'city' => 'string|max:255|nullable',
				'neighborhood' => 'string|max:255|nullable',
				'map_address' => 'string|max:255|nullable',
				'lat' => 'string|max:100|nullable',
				'lng' => 'string|max:100|nullable',
				'property_area' => 'numeric|nullable',
				'property_area_measure' => 'int|nullable',
				'land_area' => 'numeric|nullable',
				'land_area_measure' => 'int|nullable',
				'garage' => 'int|min:0|nullable',
				'garage_area' => 'numeric|nullable',
				'garage_area_measure' => 'int|nullable',
				'bedrooms' => 'int|min:0|nullable',
				'bathrooms' => 'int|min:0|nullable',
				'year_built' => 'string|max:255|nullable'
			]
		];

		$supValues = [];

		$name = 'lang';
		foreach(CustomLaravelLocalization::getSupportedLocales() as $key => $data) {
			$supValues[$name][$data['code']] = $key;
		}

		$name = 'currency_code';
		foreach(Currency::getCurrencies() as $k => $v) {
			$supValues[$name][$v['code']] = $k;
		}

		$supValues['country'] = Country::getCountryCodes('iso2');

		$name = 'property_area_measure';
		foreach(Measure::getMeasures() as $k => $v) {
			$supValues[$name][] = $v['code'];
		}
		$supValues['land_area_measure'] = $supValues[$name];
		$supValues['garage_area_measure'] = $supValues[$name];

		if($forImport) {
			$propertyFields = Property::getPropertyData();
			unset($propertyFields['status']);
			foreach($propertyFields as $f => $values) {
				foreach($values as $k => $v) {
					$supValues[$f][] = $v['id'];
				}
			}

			$features = Feature::where('lang_id', static::$defaultLang)->get();
			$name = 'features';
			foreach ($features as $feature) {
				$supValues[$name][$feature->id] = $feature->slug;
			}
		}
		$rules['allowed_values'] = $supValues;
		static::$validationRules = $rules;	
	}

	public static function getRunsByLink($linkId)
	{
		$runs = static::where('link_id', $linkId)->orderBy('id', 'desc');
		$pagination = $runs->paginate(30);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity);
		});

		return $pagination;
	}
	public static function _afterGet($run) {
		$runData = !is_array($run) ? $run->toArray() : $run;
		$runData['status_label'] = static::getStatuses()[$runData['status']];
		return $runData;
	}

	public static function runImport($id)
	{
		$links = ImportLink::where('status', 1);
		if(is_numeric($id)) {
			$links->where('id', $id);
		}
		$links = $links->get();
		if($links) {
			$curDate = DB::raw('CURRENT_DATE');
			$curTime = DB::raw('CURRENT_TIME');
			foreach ($links as $link) {
				if(!static::where('link_id', $link->id)->whereNull('ended')->exists()) {
					$run = static::create(['link_id' => $link->id, 'run_date' => $curDate, 'run_time' => $curTime]);
					if($run) {
						ImportLink::where('id', $link->id)->update(['run_id' => $run->id]);
					}
				}
			}
			return true;
		}
		return false;
	}

	public static function doExport()
	{
		static::setDefaultLang();
		static::setImportPath();
		
		static::createXlm();
		return;
	}

	public static function doImport()
	{
		$runs = static::where('status', 0)->get();
		if(!$runs) return;

		static::setDefaultLang();
		static::setImportPath();
		
		//static::createXlm();exit;
		static::setValidationRules();
		libxml_use_internal_errors(true);
		foreach ($runs as $run) {
			$runId = $run->id;
			$result = static::importLink($run);

			$data = ImportLog::getLogCounts($runId);
			$data['status'] = $result ? 1 : 2;
			$data['ended'] = DB::raw('now()');
			static::where('id', $runId)->update($data);
		}
		return;
	}

	public static function importLink($run) {
		$runId = $run->id;
		$linkId = $run->link_id;
		$importLink = ImportLink::find($linkId);
		$author = $importLink->author;
		$admin = User::find($author)->isAdmin();
		$log = ['run_id' =>$runId];

		if(!$importLink) {
			ImportLog::addError(array_merge($log, ['message' => 'Link not found']));
			return false;
		}
		$url = $importLink->link;
		if(!$url || strlen($url) < 5) {
			ImportLog::addError(array_merge($log, ['message' => 'Import Link: Url is not correct']));
			return false;
		}
		$fileName = 'import'.$linkId.'_'.$runId;

		//$xmlFileName = url('/import/example23.xml');
		$xmlOutput = static::getAndSaveFile($url, $fileName, 'main', $log);
		if(!$xmlOutput) return false;
		
		//$xml = simplexml_load_string(file_get_contents($xmlFilePath));
		$xml = simplexml_load_string($xmlOutput);

		if($xml === false) {
			$message = 'Failed loading XML: ';
			foreach(libxml_get_errors() as $error) {
				$message .= $error->message.' ';
			}
			ImportLog::addError(array_merge($log, ['message' => $message]));
			return false;
		}
		if(!isset($xml->property)) {
			ImportLog::addError(array_merge($log, ['message' => 'Error format XML']));
			return false;
		}

		$rules = static::$validationRules;

		$fieldsAliases = $rules['field_aliases'];
		$langFields = $rules['lang_fields'];
		$convertedFields = $rules['convert_fields'];
		$allowedFields = $rules['allowed_fields'];
		$allowedValues = $rules['allowed_values'];
		$defLang = static::$defaultLang;
		$fieldRules = array_merge($allowedFields, $langFields);

		//dd($fieldRules, $rules);

		$logProperty = array_merge($log, ['entity_type' => 1]);
		$i = 0;
		$error = false;
		foreach($xml->property as $property)
		{
			$data = [];
			$langs = [];
			$files = [];
			$features = [];
			$propertyDelete = (isset($property['mode']) && (string)$property['mode'] == 'delete');
			$filesReplace = false;
			$featuresAdd = false;
			$filesExist = false;
			$featuresExist = false;
			$i++;
			$error = false;

			foreach($property as $key => $value)
			{
				$error = false;
				$field = array_search($key, $fieldsAliases);
				if($field) {
					$key = $field;
				}
				switch ($key) {
					case 'languages':
						$list = [];
						foreach($value->language as $language) {
							$langCode = 0;
							foreach($language as $k => $v) {
								if($k == 'lang') {
									$langCode = (string)$v;
								} elseif(isset($langFields[$k])) {
									$val = (string)$v;
									$list[$k] = strlen($val) > 0 ? $val : null;
								} else {
									ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': unsupported field '.$k]));
									$error = true;
								}
							}
							//dd($language, $defLang, $langCode, $list, $allowedValues['lang']);
							if(!empty($langCode) && sizeof($list) > 0) {
								$id = array_search(strtolower($langCode), $allowedValues['lang']);
								if($id) {
									$langs[$id] = $list;
								} else {
									ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': unsupported currency code '.$langCode]));
									$error = true;
								}
							}
						}
						break;
					case 'files':
						$filesExist = true;
						if(isset($value['mode']) && (string)$value['mode'] == 'replace') {
							$filesReplace = true;
						}
						$j = 0;
						foreach($value->file as $v) {
							$j++;
							$path = (string)$v;
							if(strlen($path) > 5) {
								if(!in_array($path, $files)) {
									$files['f'.$j] = $path;
								}
							} else {
								ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.' file #'.$j.': no link to file specified']));
								$error = true;
							}
						}
						break;
					case 'features':
						$featuresExist = true;
						if(isset($value['mode']) && (string)$value['mode'] == 'add') {
							$featuresAdd = true;
						}
						foreach($value->feature as $v) {
							$slug = (string)$v;
							$id = array_search(strtolower($slug), $allowedValues['features']);
							if($id) {
								$features[] = $id;
							} else {
								ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': unsupported feature '.$slug]));
								$error = true;
							}
						}
						break;
					
					default:
						if(isset($allowedFields[$key])) {
							$val = (string)$value;
							if(strlen($val) == 0) $val = null;

							if(!is_null($val)) {
								if(in_array($key, $convertedFields)) {
									$id = array_search(strtoupper($val), $allowedValues[$key]);
									if($id) {
										$val = $id;
									} else {
										ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': unsupported value '.$val.' for field '.$key]));
										$error = true;
									}
								}
							}
							$data[$key] = $val;
						} else {
							ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': unsupported field '.$key]));
							$error = true;
						}
						break;
				}
				if($error) break;
			}
			//dd($error, $property, $key, $value, $data);
			if($error) continue;
			//dd($property, $data, $langs, $files, $features, $filesAdd, $featuresAdd);
			if(!isset($data['id']) || !is_numeric($data['id'])) {
				ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': id not specified']));
				continue;
			}
			$importId = $data['id'];
			$entity = ImportId::getEntity($author, 1, $importId);
			$isNew = $entity ? false : true;
			$entityId = $isNew ? 0 : $entity['id'];

			//$logProperty = array_merge($log, ['import_id' => $importId, 'entity_id' => $entityId]);
			$logProperty = array_merge($log, ['import_id' => $importId, 'entity_type' => 1, 'entity_id' => $entityId]);

			if(!$isNew) {
				if(!$admin && $entity['author'] != $author) {
					ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': link author does not have permissions to edit property']));
					continue;
				}	
				if($entity['status'] == 5) {
					ImportLog::saveLog(array_merge($logProperty, ['result' => 5, 'message' => 'Property #'.$i.': property already deleted']));
					continue;
				}
				if($propertyDelete) {
					Property::where('id', $entityId)->update(['status' => 5]);
					ImportLog::saveLog(array_merge($logProperty, ['result' => 3]));
					continue;
				}
			}
			//dd($data, $entity);

			unset($data['id']);
			if($isNew) {
				$data['status'] = 1;
				$data['author'] = $author;
			} else {
				$data = array_merge($entity, $data);
			}
			if(sizeof($langs) > 0) {
				reset($langs);
				$mainLang = !$isNew || isset($langs[$defLang]) ? $defLang : key($langs);

				if(isset($langs[$mainLang])) {
					foreach($langs[$mainLang] as $k => $v) {
						$data[$k] = $v;
					}	
					unset($langs[$mainLang]);
				}
			}

			$validator = Validator::make($data, $fieldRules);
			if($validator->fails()) {
				ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': '.json_encode($validator->errors()->toArray())]));
				continue;
			}

			if($featuresExist) {
				$data['features'] = $featuresAdd ? array_merge($data['features'], $features) : $features;
			}
			//dd($files, $filesAdd, $data, $entity);

			$uploads = [];
			$logUploads = $logProperty;
			$logUploads['entity_type'] = 2;
			if(!$isNew && isset($data['uploadsList']) && is_array($data['uploadsList'])) {
				foreach($data['uploadsList'] as $upload) {
					$id = $upload['id'];
					if($filesReplace) {
						Upload::deleteUpload([], $id);
						ImportLog::saveLog(array_merge($logUploads, ['entity_id' => $id, 'result' => 3, 'message' => $upload['name']]));
					} elseif($featuresExist) {
						$url = ImportLog::where([
							['import_id', $importId],
							['entity_type', 2],
							['entity_id', $id],
							['result', 1]])->value('message');
						
						$fileId = $url ? array_search($url, $files) : false;					
						if($fileId) {
							$uploads[] = $id;
							if($upload['is_featured']) {
								$data['featured_image'] = $id;
							}
							unset($files[$fileId]);
						}
						else {
							Upload::deleteUpload([], $id);
							ImportLog::saveLog(array_merge($logUploads, ['entity_id' => $id, 'result' => 3, 'message' => $upload['name']]));
						}
					} else {
						$uploads[] = $id;
						if($upload['is_featured']) {
							$data['featured_image'] = $id;
						}
					}
				}
			}

			//dd($files, $filesAdd, $data, $entity);
			if(sizeof($files) > 0) {
				foreach($files as $id => $filePath) {
					if($filePath)
					$uploadId = static::getAndSaveFile($filePath, $fileName.'_'.$i.'_'.substr($id, 1), 'upload', $logUploads, $entityId);
					if($uploadId) {
						$uploads[] = $uploadId;
					}
				}
			}
			$data['photos'] = $uploads;

			//dd($featuresAdd, $features, $data, $entity);
			$property = Property::saveItem($data, true, $langs);

			if(isset($property['id'])) {
				$id = $property['id'];
				ImportLog::saveLog(array_merge($logProperty, ['entity_id' => $id, 'result' => $isNew ? 1 : 2]));
				ImportId::saveEntityId($author, 1, $importId, $id);
			} else {
				ImportLog::addError(array_merge($logProperty, ['message' => 'Property #'.$i.': Error occurred while saving the property.'.json_encode($property)]));
			}
			//dd($data);
		}
		return !$error;
	}

	private static function getAndSaveFile($url, $fileName, $type = 'main', $log = [], $propertyId = null)
	{
		$fullUrl = substr($url, 0, 4) != 'http' ? 'http://' . $url : $url;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $fullUrl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if($info['http_code'] != 200) {
			ImportLog::addError(array_merge($log, ['message' => $url.' - HTTP Code:'.$info['http_code']]));
			return false;
		}

		if($type == 'main') {
			$filePath = static::$importPath.'/'.$fileName.'_'.date('YmdHis').'.xml';
			if(!file_put_contents($filePath, $output)) return false;
			chmod($filePath, 0775);
			return $output;

		} else {
			$len = $info['download_content_length'];
			if($len < 1000) {
				ImportLog::addError(array_merge($log, ['message' => $url.' - Too small file: '.$len]));
				return false;
			}
			//dd($fullUrl, $len, $fullUrl.((string)$len));
			/*$hash = md5($fullUrl.((string)$len));
			if($propertyId) {
				$uploadId = UploadProperty::select('u.id')
					->join('uploads as u', 'u.id', '=', 'uploads_properties.upload_id')
					->join('import_log as l', 'l.entity_id', '=', 'u.id')
					->where([
						['uploads_properties.property_id', $propertyId],
						['l.import_id', $log['import_id']],
						['l.entity_type', 2],
						['l.result', 1],
						['l.hash', $hash]])->first();
				//dd($uploadId);
				if($uploadId) {
					$id = $uploadId->id;
					ImportLog::saveLog(array_merge($log, ['entity_id' => $id, 'result' => 1, 'message' => $url.': has already been loaded']));
					return $id;
				}
			}*/

			$mimeType = $info['content_type'];
			$uploadType = 0;
			foreach(static::$uploadTypes as $k => $v) {
				if(strpos($mimeType, $k) === 0) {
					$uploadType = $v;
				}
			}
			//$ext = pathinfo($url, PATHINFO_EXTENSION); 
			//$name = pathinfo($url, PATHINFO_FILENAME);
			$path = parse_url($url, PHP_URL_PATH);
			$ext = pathinfo($path, PATHINFO_EXTENSION); 
			//$name = pathinfo($path, PATHINFO_FILENAME);
			//$fileName = Upload::getCorrectFileName($name, $ext);
			//$fileName = static::$tempPath.'/'.$fileName.'_'.date('YmdHis').'.'.$ext;
			
			$uploadDir = Upload::getUploadsDir();
			$fileName = Upload::getUploadsDir().'/'.$fileName.'_'.date('YmdHis').'.'.$ext;
			$filePath = static::$uploadsPath.'/'.$fileName;
			if(!file_put_contents($filePath, $output)) return false;
			chmod($filePath, 0775);
			$item = new Upload();
			$item->type = $uploadType;
			$item->name = $fileName;
			$item->save();
			if($item && $item->id) {
				$id = $item->id;
				//dd(['entity_id' => $id, 'result' => 1, 'message' => $url, 'hash' => $hash]);
				ImportLog::saveLog(array_merge($log, ['entity_id' => $id, 'result' => 1, 'message' => $url]));
				return $id;
			} else {
				ImportLog::addError(array_merge($log, ['message' => $url.': error creating record in DB']));
			}	
		}

		return false;
	}

	public static function arrayToXml(array $arr, \SimpleXMLElement $xml)
	{
		foreach ($arr as $k => $v) {
			$pos = strpos($k, '>');
			$key = ($pos === false ? $k : substr($k, 0, $pos));
			$pos = strpos($key, '|');
			if(is_null($v)) $v = '';
			else if(is_bool($v)) $v = $v ? 1 : 0;

			if($pos > 0) {
				$attr = explode('=', substr($key, $pos + 1));
				$key = substr($key, 0, $pos);
			} else $attr = false;
			$node = is_array($v)
				? static::arrayToXml($v, $xml->addChild($key))
				: $xml->addChild($key, $v);
			if(is_array($attr) && sizeof($attr) == 2) {
				$node->addAttribute($attr[0], $attr[1]);
			}
		}
		return $xml;
	}

	public static function createXlm()
	{
		$properties = Property::where('id', '=', 2138)->get()->toArray();
		if(!$properties) return;
		
		static::setValidationRules(false);
		$rules = static::$validationRules;

		$fieldsAliases = $rules['field_aliases'];
		$langFields = $rules['lang_fields'];
		$convertedFields = $rules['convert_fields'];
		$allowedFields = $rules['allowed_fields'];
		$allowedValues = $rules['allowed_values'];
		$defLang = static::$defaultLang;
		$defLangCode = $allowedValues['lang'][static::$defaultLang];

		//dd($rules, $defLangCode);
		$export = [];
		$i = 0;
		foreach($properties as $property) {
			$id = $property['id'];
			$data = [];

			$property['price'] = is_null($property['price_local']) ? $property['price_default'] : $property['price_local'];

			foreach(Property::$hasArea as $name) {
				$localName = $name . '_local';
				$defaultName = $name . '_default';
				$property[$name] = is_null($property[$localName]) ? $property[$defaultName] : $property[$localName];
			}

			$langId = 'language>0';
			$langs = [$langId => ['lang' => $defLangCode]];
			foreach($langFields as $field => $r) {
				$langs[$langId][$field] = $property[$field];
			}

			foreach($property as $field => $value) {
				if(isset($allowedFields[$field])) {
					if(in_array($field, $convertedFields)) {
						$value = isset($allowedValues[$field]) && isset($allowedValues[$field][$value]) ? $allowedValues[$field][$value] : null; 
					}
					$data[isset($fieldsAliases[$field]) ? $fieldsAliases[$field] : $field] = $value;
				}
			}
			$records = PropertyLang::where('property_id', $id)->get();
			if($records) {
				$f = 1;
				foreach($records->toArray() as $values) {
					$langId = 'language>'.$f;
					$langs[$langId]['lang'] = $allowedValues['lang'][$values['lang_id']];
					foreach($langFields as $field => $r) {
						$langs[$langId][$field] = $values[$field];
					}
					$f++;
				}
			}

			$features = [];
			$records = FeatureProperty::select('slug')->where('property_id', $id)->join('features as f', 'f.feature_id', '=', 'features_properties.feature_id')->where('lang_id', $defLang)->get();
			if($records) {
				$f = 0;
				foreach($records->toArray() as $values) {
					$features['feature>'.$f] = $values['slug'];
					$f++;
				}
			}

			$files = [];
			$records = Upload::getUploadedImages($id, 'properties');
			if($records) {
				$f = 0;
				foreach($records as $values) {
					$fileId = 'file>'.$f;
					$files[$fileId] = url('uploads/'.$values['name']);
					$f++;
				}
			}
			$propId = 'property>'.$i;
			$export[$propId] = $data;
			if(sizeof($langs) > 0) {
				$export[$propId]['languages'] = $langs;
			}
			if(sizeof($features) > 0) {
				$export[$propId]['features'] = $features;
			}
			if(sizeof($files) > 0) {
				//$export[$propId]['files|mode=replace'] = $files;
				$export[$propId]['files'] = $files;
			}
			$i++;
		}
		//dd(url(uploads/));

		$xmlFileName = static::$importPath.'/test.xml';
		file_put_contents($xmlFileName, static::arrayToXml($export, new \SimpleXMLElement('<properties/>'))->asXML());
	}
}

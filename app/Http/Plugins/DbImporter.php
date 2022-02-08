<?php

namespace App\Http\Plugins;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Runner\Exception;
use Property;
use User;
use Role;
use Upload;
use UploadProperty;
use Feature;
use Country;
use AgencyAgents;

class DbImporter extends Model
{
	public $timestamps = false;

	protected $table = 'db_importer';

	public $fillable = [
		'old_id', 'item_type', 'item_subtype', 'new_id', 'old_attachment_link',
	];

	public $oldFeaturesKeys = ['',12,171,332,179,202,270,206,259,252,268,100,253,186,284,101,176,178,175,216,103,47,225,37,180,210,204,199,169,46,209,174,215,286,198,272,258,273,327,173,79,278,102,205,275,194,250,99,220,288,267,207,195,82,208,203,172,177,170];

	public static $mysql = null;
	public static $host = 'localhost';
	public static $username = 'root';
	public static $password = 'root';
	public static $database = 'thelead1_wp7';

	public static $defImportType = null;
	public static $defCount = null;
	public static $defOffset = 0;
	public static $defLimit = 500;

	public static $importType = null;
	public static $count = null;
	public static $offset = null;
	public static $limit = null;

	public static $uploadsPath = 'https://premizez.com/wp-content/uploads/';
	public static $response = ['message' => 'Done', 'errors_exist' => false,];
	public static $usersTypesToRoles = [
		'houzez_agency' => 'agency',
		'houzez_agent' => 'agent',
		'building_company' => 'building_company',
		'building_agent' => 'building_company_agent',
		'architect_firm' => 'architect_firm',
		'architect_agent' => 'architect',
		'project_home' => 'project_home_company',
		'project_home_agent' => 'project_home_company_agent',
		'property_management' => 'property_management',
		'vacation_home' => 'vacation_home_company',
		//'houzez_packages',
		//'houzez_partner',
	];

	public static function successResponse() {
		static::$response = [
			'message' => 'Done',
			'errors_exist' => false,
			'count' => static::$count,
			'offset' => static::$offset,
			'limit' => static::$limit,
		];
		return static::$response;
	}

	public static function dbConnection() {
		static::$mysql = mysqli_connect(static::$host, static::$username, static::$password, static::$database);
		if(!static::$mysql) {
			throw new Exception(mysqli_error(static::$mysql));
		}
		return static::$mysql;
	}

	public static function setVariables($data) {
		static::$importType = !empty($data['type']) ? $data['type'] : static::$defImportType;
		static::$limit = !empty($data['limit']) ? $data['limit'] : static::$defLimit;
		static::$offset = !empty($data['offset']) ? $data['offset'] : static::$defOffset;
		static::$count = !empty($data['count']) ? $data['count'] : static::$defCount;
		static::getTableItemsCount(static::$importType);
	}

	public static function clearVariables() {
		if(static::$offset >= static::$count) {
			static::$importType = static::$defImportType;
			static::$limit = static::$defLimit;
			static::$offset = static::$defOffset;
		}
	}

	public static function getTableItemsCount() {
		if(static::$count === null) {
			$itemsCount = null;

			switch(static::$importType) {
				case 'properties':case 'properties_attachment':
				$itemsCount = static::makeMysqliQuery('SELECT COUNT(*) as count FROM wpc4_posts AS p WHERE p.post_type = "property" AND p.post_status = "publish"');
				break;
				case 'users': case 'users_attachment':
				$itemsCount = static::makeMysqliQuery('SELECT COUNT(*) as count FROM wpc4_posts AS p WHERE post_type in ("'.implode('","', array_keys(static::$usersTypesToRoles)).'")');
				//$itemsCount = static::makeMysqliQuery('SELECT COUNT(*) as count FROM wpc4_posts AS p WHERE post_type in ("architect_agent")');
				break;
				case 'add_watermarks':
					static::$count = Upload::where('type', 1)->where('watermark', null)->count();
					break;
				case 'properties_users':
					static::$count = static::where('item_type', 'user')->count();
					break;
				case 'agency_agents':
					$itemsCount = static::makeMysqliQuery('SELECT COUNT(*) FROM wpc4_postmeta WHERE meta_key = \'fave_agent_agencies\'');
					break;
				default:
					break;
			}
			if(!empty($itemsCount)){
				static::$count = (int) mysqli_fetch_row($itemsCount)[0];
			}
		}
		return static::$count;
	}

	public static function getDataFromOldDb($data) {
		try {
			static::dbConnection();
			static::setVariables($data);

			if(static::$count > 0) {
				switch(static::$importType) {
					case 'users':
						static::$response = static::_importUsers();
						break;
					case 'users_attachment':
						static::$response = static::_importUsersImages();
						break;
					case 'properties':
						static::$response = static::_importProperties();
						break;
					case 'properties_attachment':
						static::$response = static::_importPropertiesImages();
						break;
					case 'properties_users':
						static::$response = static::_importPropertiesUsers();
						break;
					case 'agency_agents':
						static::$response = static::_importAgencyAgents();
						break;
					default:
						break;
				}
			}
			static::clearVariables();
			mysqli_close(static::$mysql);
		} catch(Exception $e) {
			static::$response = ['message' => $e->getMessage(), 'errors_exist' => true];
		}
		return static::$response;
	}

	public static function addWatermarksToImages($data) {
		try {
			static::setVariables($data);
			static::$response = static::_addWatermarks();
			static::clearVariables();
		} catch(Exception $e) {
			static::$response = ['message' => $e->getMessage(), 'errors_exist' => true];
		}
		return static::$response;
	}

	public static function _importProperties() {
		$propertiesChunk = static::makeMysqliQuery('SELECT
p.id as id,
p.post_title as title,
p.post_author as author,
p.post_status as status,
840 as currency_code,
1 as property_area_measure,
1 as land_area_measure,
1 as garage_area_measure,
post_modified_gmt as created_at,
post_modified_gmt as updated_at,
p.post_content as description
FROM wpc4_posts AS p
WHERE p.post_type = "property"
ORDER BY p.id
LIMIT '.static::$limit.'
OFFSET ' . static::$offset);

		while($item = mysqli_fetch_assoc($propertiesChunk)) {
			$oldPropertyId = $item['id'];

			if(static::where([['old_id', '=', $oldPropertyId], ['item_subtype', '=', 'property']])->value('new_id')) continue;

			$propertyData = [];
			$images = [];
			$features = [];

			// Properties Meta Fields Data
			$metaQuery = static::makeMysqliQuery('SELECT meta_key, meta_value FROM wpc4_postmeta where post_id = '.$oldPropertyId);
			while($metaData = mysqli_fetch_assoc($metaQuery)) {
				switch($metaData['meta_key']) {
					case 'fave_property_id':
						//$propertyData['id'] = $propertyData['id'] == $metaData['meta_value'] ? $metaData['meta_value'] : $propertyData['id'];
						break;
					case 'fave_property_bedrooms': case 'fave_property_bathrooms': case 'fave_property_garage':
					$propertyData[str_replace('fave_property_', '', $metaData['meta_key'])] = (int) $metaData['meta_value'];
					break;
					case 'fave_property_address': case 'fave_property_map_address':
					$propertyData[str_replace('fave_property_', '', $metaData['meta_key'])] = $metaData['meta_value'];
					break;
					case 'fave_property_agency':
						$relationId = $metaData['meta_value'];
						break;
					case 'fave_property_price':
						$propertyData['price'] = $metaData['meta_value'];
						$propertyData['price'] = is_numeric($propertyData['price']) ? $propertyData['price'] : 1;
						// TODO: fix cases when price is a text
						break;
					case 'fave_property_sec_price':
						$propertyData['price_second'] = (float) $metaData['meta_value'];
						break;
					case 'fave_property_price_prefix':
						$propertyData['price_before'] = $metaData['meta_value'];
						break;
					case 'fave_property_price_postfix':
						$propertyData['price_after'] = $metaData['meta_value'];
						break;
					case 'fave_property_size':
						$propertyData['property_area'] = $metaData['meta_value'];
						break;
					case 'fave_property_land':
						$propertyData['land_area'] = $metaData['meta_value'];
						break;
					case 'fave_property_garage_size':
						$propertyData['garage_area'] = $metaData['meta_value'];
						break;
					case 'fave_property_property_postfix': case 'fave_property_land_postfix':
					//$propertyData['property_area_measure'] = $metaData['meta_value'];
					//$propertyData['land_area_measure'] = $metaData['meta_value'];
					break;
					case 'fave_property_location':
						$propertyData = static::getCoordsFromString($metaData['meta_value'], $propertyData);
						break;
					case 'fave_property_country':
						$country = $metaData['meta_value'];
						$countriesList = Country::all();
						$countriesList = $countriesList->count() ? $countriesList->toArray() : [];

						foreach($countriesList as $c) {
							if($country == $c['name'] || $country == $c['iso2'] || $country == $c['iso3']) {
								$propertyData['country'] = $c['id'];
							}
						}
						break;
					case 'fave_property_zip':
						$propertyData['postal_code'] = $metaData['meta_value'];
						break;
					case 'fave_property_images':
						$images[] = $metaData['meta_value'];
						break;
					default:
						break;
				}
			}

			// Properties Taxonomy Data
			$taxQuery = static::makeMysqliQuery('SELECT
wpc4_term_taxonomy.taxonomy,
wpc4_terms.name,
wpc4_terms.slug
FROM wpc4_term_relationships
INNER JOIN wpc4_term_taxonomy
ON wpc4_term_relationships.term_taxonomy_id = wpc4_term_taxonomy.term_taxonomy_id
INNER JOIN wpc4_terms
ON wpc4_term_taxonomy.term_id = wpc4_terms.term_id
WHERE object_id = '.$oldPropertyId.'
ORDER BY taxonomy');
			while($taxData = mysqli_fetch_assoc($taxQuery)) {
				switch($taxData['taxonomy']) {
					case 'property_city':
						$propertyData['city'] = $taxData['name'];
						break;
					case 'property_feature':
						$featureKey = array_search($taxData['term_id'], static::$oldFeaturesKeys);

						if($featureKey) {
							$features[] = $featureKey;
						}
						break;
					case 'property_label':
						break;
					case 'property_status': case 'property_type':
					$propertyStatuses = Property::getPropertyData($taxData['taxonomy']);
					$status = null;
					foreach($propertyStatuses as $k => $v) {
						if((!empty( $v['label']) &&  $v['label'] == $taxData['name']) || (!empty( $v['slug']) &&  $v['slug'] == $taxData['slug'])) {
							$propertyData[$taxData['taxonomy']] =  $v['id'];
						}
					}
					break;
					default:
						break;
				}
			}
			// Prepare Property to Import
			$propertyData = array_merge($item, $propertyData);
			$propertyData['title'] = !empty($propertyData['title']) ? $propertyData['title'] : 'No title';
			$propertyData['status'] = static::getStatusId($propertyData['status']);
			$propertyData['price'] = !empty($propertyData['price']) ? $propertyData['price'] : 1;
			$propertyData['author'] = !empty($propertyData['author']) ? $propertyData['author'] : 1;
			$propertyData['photos'] = [];
			$propertyData['featured_image'] = null;
			$featuresIds = Feature::getFeaturesDataByParam('slug', $features, 'feature_id');
			$propertyData['features'] = !empty($featuresIds) ? $featuresIds->toArray() : $featuresIds;
			$propertyData = static::arrayMapUtf8Encode($propertyData);

			// Import Property and User
			$property = Property::saveItem($propertyData, true);
			$newAuthorId = static::where([['old_id', '=', $oldPropertyId], ['item_subtype', '=', 'property']])->value('new_id');
			$newAuthorId = !empty($newAuthorId) ? $newAuthorId : 1;
			Property::where('id', $property['id'])->update(['author' => $newAuthorId]);
			static::addRecordToDbImporterTbl([
					'old_id' => $oldPropertyId,
					'item_type' => 'property',
					'item_subtype' => 'property',
					'new_id' => $property['id']]
			);
			//dd('done');
		}
		return static::successResponse();
	}

	public static function _importUsers() {
		$userQuery = static::makeMysqliQuery('SELECT id FROM wpc4_posts WHERE post_type in ("'.implode('","', array_keys(static::$usersTypesToRoles)).'") LIMIT '.static::$limit.' OFFSET '.static::$offset);
		//$userQuery = static::makeMysqliQuery('SELECT id FROM wpc4_posts WHERE post_type in ("architect_agent") LIMIT '.static::$limit.' OFFSET '.static::$offset);

		while($userData = mysqli_fetch_assoc($userQuery)) {
			$userId = $userData['id'];
			$userQueryResult = static::makeMysqliQuery('SELECT
p.id as id,
p.post_title as title,
p.post_type as role_name,
p.post_status as status,
post_modified_gmt as created_at,
post_modified_gmt as updated_at,
p.post_content as description
FROM wpc4_posts AS p
WHERE p.id = '.$userId);
			$userData = mysqli_fetch_assoc($userQueryResult);
			$user = ['id' => 1];

			if(!empty($userData)) {
				if($newUserId = static::where([['old_id', '=', $userData['id']], ['item_type', '=', 'user']])->value('new_id')) {
					$user['id'] = $newUserId;
					$userData['new_id'] = $newUserId;
					//return $user;
				}
				// User's Meta Fields Data
				$postMetaQuery = static::makeMysqliQuery('SELECT * FROM wpc4_postmeta where post_id = '.$userId);
				$type = $userData['role_name'];
				while($postMetaData = mysqli_fetch_assoc($postMetaQuery)) {
					switch($postMetaData['meta_key']) {
						case 'houzez_user_meta_id':
							// request to users table
							break;
						case 'fave_'.$type.'_position':
						case 'fave_'.$type.'_language': case 'fave_'.$type.'_email':
						case 'fave_'.$type.'_phone': case 'fave_'.$type.'_mobile': case 'fave_'.$type.'_fax': case 'fave_'.$type.'_tax_no':
						case 'fave_'.$type.'_facebook': case 'fave_'.$type.'_instagram': case 'fave_'.$type.'_twitter':
						case 'fave_'.$type.'_linkedin': case 'fave_'.$type.'_googleplus': case 'fave_'.$type.'_pinterest':
						case 'fave_'.$type.'_youtube': case 'fave_'.$type.'_vimeo':
						case 'fave_'.$type.'_address': case 'fave_'.$type.'_map_address':
						$userData[str_replace('fave_'.$type.'_', '', $postMetaData['meta_key'])] = $postMetaData['meta_value'];
						break;
						case 'fave_'.$type.'_company':
							$userData['company_name'] = $postMetaData['meta_value'];
							break;
						case 'fave_'.$type.'office_num':
							$userData['phone'] = $postMetaData['meta_value'];
							break;
						case 'fave_'.$type.'_web':case 'fave_'.$type.'_website':
						$userData['website'] = $postMetaData['meta_value'];
						break;
						case 'fave_'.$type.'_location':
							$userData = static::getCoordsFromString($postMetaData['meta_value'], $userData);
							break;
						case 'fave_'.$type.'_licenses':
							$userData['license'] = $postMetaData['meta_value'];
							break;
						default:
							break;
					}
				}
				// Prepare User to Import
				$roleName = static::$usersTypesToRoles[$userData['role_name']];
				$userData['company_name'] = !empty($userData['company_name']) ? $userData['company_name'] : '';
				$userData['title'] = !empty($userData['title']) ? $userData['title'] : '';
				$titleParts = explode(' ', $userData['title'], 2);
				$userData['first_name'] = !empty($titleParts[0]) ? $titleParts[0] : '';
				$userData['last_name'] = !empty($titleParts[1]) ? $titleParts[1] : '';

				$usernameFromParts = strtolower($userData['title']);
				$usernameFromParts = preg_replace('/[^a-zA-Z0-9-_\.\s]/','', $usernameFromParts);
				$usernameFromParts = explode(' ', $usernameFromParts);
				$username1 = !empty($usernameFromParts[0]) ? $usernameFromParts[0].'_' : '';
				$username2 = !empty($usernameFromParts[1]) ? $usernameFromParts[1] : 'mail';
				$username = $username1.$username2;
				$username = str_replace(' ', '_', $username);
				$email = !empty($userData['email']) ? $userData['email'] : $username.'@gmail.com';
				$email = utf8_encode($email);
				$userIdByEmail = User::where('email', $email)->value('id');

				if($userIdByEmail && $userIdByEmail != $newUserId) {
					$usernameNew = $username;
					$iter = 0;
					$index = '_1';

					while(User::where('email', $email)->value('id')) {
						$usernameNew = $username.$index;
						$email = $usernameNew.'@gmail.com';
						$iter++;
						$index = '_'.$iter;
					}
					//$username = $usernameNew;
				}
				$userData['name'] = $username;
				$userData['email'] = $email;
				$userData['password'] = bcrypt($username.'_pass');
				$userData['role_id'] = static::getRoleIdByName($userData['role_name']);
				$userData['status'] = static::getStatusId($userData['status']);
				$userData = static::arrayMapUtf8Encode($userData);

				$user = User::saveUserProfile($userData, $roleName);

				static::addRecordToDbImporterTbl([
					'old_id' => $userData['id'],
					'item_type' => 'user',
					'item_subtype' => $roleName,
					'new_id' => $user['id']
				]);
				//dd('done');
			}
		}
		return static::successResponse();
	}

	public static function _importPropertiesImages() {
		$result = static::makeMysqliQuery('SELECT p.id FROM wpc4_posts AS p WHERE p.post_type = "property" AND p.post_status = "publish" ORDER BY p.id LIMIT '.static::$limit.' OFFSET '.static::$offset);

		while($item = mysqli_fetch_assoc($result)) {
			$propertyId = $item['id'];
			$images = static::makeMysqliQuery('SELECT meta_value FROM wpc4_postmeta AS pm WHERE pm.post_id = '.$propertyId.' AND pm.meta_key = "fave_property_images"');
			$imagesIds = mysqli_fetch_all($images);
			$imagesIds = array_map(function($value) {
				return is_array($value) ? $value[0] : $value;
			}, $imagesIds);

			foreach($imagesIds as $imgId) {
				static::_importImage($imgId, 'user_photo', $params = ['item_type' => 'post_attachment', 'property_id' => $propertyId]);
			}
			//dd('done');
		}
		return static::successResponse();
	}

	public static function _importUsersImages() {
		$userQuery = static::makeMysqliQuery('SELECT * FROM wpc4_posts AS p WHERE post_type in ("'.implode('","', array_keys(static::$usersTypesToRoles)).'") LIMIT '.static::$limit.' OFFSET '.static::$offset);
		while($userData = mysqli_fetch_assoc($userQuery)) {
			$userData = array_change_key_case($userData, CASE_LOWER);
			$userId = $userData['id'];

			// User Meta Fields Data
			$userMetaQuery = static::makeMysqliQuery('SELECT meta_value as photo FROM wpc4_postmeta where post_id = ' . $userId . ' AND meta_key = \'_thumbnail_id\'');
			while ($userMetaData = mysqli_fetch_assoc($userMetaQuery)) {
				static::_importImage($userMetaData['photo'], 'user_photo', $params = ['user_id' => $userId]);
			}
		}
		return static::successResponse();
	}

	public static function _importImage($imageId, $params = []) {
		if(!empty($imageId)) {
			if(static::where([['old_id', '=', $imageId], ['item_type', '=', $params['item_type']], ['item_subtype', '=', 'image']])->value('new_id')) return false;

			$attachmentQuery = static::makeMysqliQuery('SELECT * FROM wpc4_posts WHERE id = '.$imageId);
			$attachmenData = mysqli_fetch_assoc($attachmentQuery);
			$attachmenData = !empty($attachmenData) ? array_change_key_case($attachmenData, CASE_LOWER) : $attachmenData;
			$imageUrl = !empty($attachmenData['guid']) ? $attachmenData['guid'] : null;

			if(!$imageUrl) {
				$attachmentMetaQuery = static::makeMysqliQuery('SELECT meta_key, meta_value FROM wpc4_postmeta WHERE post_id = '.$imageId);
				while($attachmentMeta = mysqli_fetch_assoc($attachmentMetaQuery)) {
					if($attachmentMeta['meta_key'] == '_wp_attached_file') {
						$imageUrl = !empty($attachmentMeta['meta_value']) ? $attachmentMeta['meta_value'] : null;
					}
				}
			}
			if($imageUrl) {
				$path = preg_replace('/.*uploads\//', '', $imageUrl);
				$path = utf8_encode($path);
				$pathInfo = pathinfo($path);

				if(!empty($pathInfo['filename']) && !empty($pathInfo['extension'])) {
					$name = Upload::getCorrectFileName($pathInfo['filename'], $pathInfo['extension'], 'db');

					$upload = new Upload();
					$upload->type = 1;
					$upload->name = $name;
					$upload->save();
					$uploadId = $upload->id;

					if($params['item_type'] == 'user_photo' && !empty($params['user_id'])) {
						$newUserId = static::where('old_id', $params['user_id'])->value('new_id');
						User::where('id', $newUserId )->update(['photo' => $uploadId]);
					}
					if($params['item_type'] == 'post_attachment' && !empty($params['property_id'])) {
						$uploadProperty = new UploadProperty;
						$uploadProperty->fill(['property_id' => $params['property_id'], 'upload_id' => $uploadId]);
						$uploadProperty->save();
					}

					static::addRecordToDbImporterTbl([
						'old_id' => $imageId,
						'item_type' => $params['item_type'],
						'item_subtype' => 'image',
						'new_id' => $uploadId,
						'old_attachment_link' => $path,
					]);
					//dd('done');
				}
			}
		}
	}

	public static function _importAgencyAgents() {
		$agencyAgentsChunkQuery = static::makeMysqliQuery('SELECT meta_value as agency_id, post_id as agent_id FROM wpc4_postmeta WHERE meta_key = \'fave_agent_agencies\' ORDER BY agency_id LIMIT '.static::$limit.' OFFSET ' . static::$offset);

		while($item = mysqli_fetch_assoc($agencyAgentsChunkQuery)) {
			$agent = static::where('old_id', $item['agent_id'])->value('new_id');
			$agency = static::where('old_id', $item['agency_id'])->value('new_id');
			if($agent && $agency) {
				AgencyAgents::saveAgency($agent, $agency, 1);
			}

		}
		return static::successResponse();
	}

	public static function _importPropertiesUsers() {
		$usersOldNewIds = static::where('item_type', 'user')->offset(static::$offset)->limit(static::$limit)->get();
		$usersOldNewIds = !empty($usersOldNewIds) ? $usersOldNewIds->toArray() : [];

		foreach($usersOldNewIds as $uid) {
			$propertiesChunk = static::makeMysqliQuery('SELECT post_id FROM wpc4_postmeta where meta_key = \'fave_property_agency\' and meta_value = '.$uid['old_id']);
			$oldPropertyIds = array_map('array_shift', mysqli_fetch_all($propertiesChunk));
			$newPropertyIds = static::whereIn('old_id', $oldPropertyIds)->pluck('new_id');
			$newPropertyIds = !empty($newPropertyIds) ? $newPropertyIds->toArray() : [];
			Property::whereIn('id', $newPropertyIds)->update(['author' => $uid['new_id']]);
		}
		return static::successResponse();
	}

	public static function _addWatermarks() {
		//$images = Upload::where('type', 1)->where('watermark', null)->offset(static::$offset)->limit(static::$limit)->get();
		$images = Upload::where('type', 1)->where('watermark', null)->limit(static::$limit)->get();

		if(!empty($images) && $images->count()) {
			$images = $images->toArray();

			foreach($images as $img) {
				if(empty($img['watermark']) && $img['watermark'] != 1) {
					if(file_exists(Upload::getUploadsPath() . '/' . $img['name'])) {
						Upload::addWatermark($img['name'], $img['id']);
					} else {
						Upload::where('id', $img['id'])->update(['watermark' => 0]);
					}
				}
			}
		}
		return static::successResponse();
	}

	public static function makeMysqliQuery($queryString) {
		$query = mysqli_query(static::$mysql, $queryString);

		if(!$query) {
			throw new Exception('Query Error: '.mysqli_error(static::$mysql));
		}
		return $query;
	}

	public static function addRecordToDbImporterTbl($data) {
		$importerId = static::where([
			['old_id', '=', $data['old_id']],
			['item_type', '=', $data['item_type']],
			['item_subtype', '=', $data['item_subtype']],
			['new_id', '=', $data['new_id']]
		])->value('id');
		$importer = static::findOrNew($importerId);
		$importer->fill($data);
		$importer->save();
	}

	public static function getStatusId($status) {
		$statuses = Property::getPropertyData('status');
		return !empty($statuses[$status]) ? $statuses[$status]['id'] : 4;
	}

	public static function getRoleIdByName($name) {
		$roleName = static::$usersTypesToRoles[$name];
		$roleId = Role::where('name', $roleName)->value('id');
		return !empty($roleId) ? $roleId : 14;
	}

	public static function getCoordsFromString($coords, $data) {
		$lat_lng = explode(',', $coords);
		$data['lat'] = !empty($lat_lng[0]) ? $lat_lng[0] : null;
		$data['lng'] = !empty($lat_lng[1]) ? $lat_lng[1] : null;
		return $data;
	}

	public static function arrayMapUtf8Encode($data) {
		return array_map(function($value) {
			return is_string($value) ? utf8_encode($value) : $value;
		}, $data);
	}

	public static function getImportedImagesList() {
		echo 'start...';
		$count = DbImporter::where('item_subtype', 'image')->count();
		$limit = 2500;
		$offset = 0;
		$filename = '/var/www/html/public/images_list.php';
		file_put_contents($filename, '');
		chmod($filename, 0777);
		$handler = fopen($filename, "w+");
		fwrite($handler, '<?php return [');

		while($offset < $count) {
			$data = DbImporter::where('item_subtype', 'image')->limit($limit)->offset($offset)->pluck('old_attachment_link');

			foreach($data as $d) {
				fwrite($handler, '"'.$d.'",');
			}
			$offset += $limit;
		}
		fwrite($handler, '];');
		fclose($handler);
		echo 'Done';
		exit;
	}
}

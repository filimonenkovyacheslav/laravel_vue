<?php

namespace App\Http\Models\Profile;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Cviebrock\EloquentSluggable\Sluggable;
use App;
use Validator;
use Hash;
use CustomLaravelLocalization;
use Property;
use Franchise;
use Auth;
use Role;
use Agency;
use Agent;
use Artist;
use Seller;
use Wineseller;
use Furnitureseller;
use Brand;
use Projects;
use JobEntity;
use ArchitectFirm;
use Architect;
use BuildingCompany;
use BuildingCompanyAgent;
use Professional;
use ProjectHomeCompany;
use ProjectHomeAgent;
use PropertyManagement;
use VacationHomeCompany;
use Profession;
use ProfessionUser;
use SearchHelper;
use Upload;
use BaseModel;
use ElasticSearchHelper;
use AgencyAgents;
use DB;
use PropertyFavorite;
use Ads;
use Art;
use ArtCategory;
use Product;
use ProductCategory;
use Wine;
use WineCategory;
use Furniture;
use FurnitureCategory;
use Good;
use GoodCategory;
use Design;
use DesignCategory;
use DesignCompany;
use Gallery;
use Country;
use AddressKeyword;
use QuotesRequest;
use News;
use SimpleKeyword;

class User extends Authenticatable
{
    use Notifiable;
	use Sluggable;

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return ['slug' => ['source' => ['first_name', 'last_name']]];
	}

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'role_id', 'name', 'email', 'password', 'status', 'label', 'slug',
		'first_name', 'last_name', 'photo', 'header_media',
		'address', 'map_address', 'lat', 'lng', 'country', 'city', 'language',
		'phone', 'tax_number', 'fax_number', 'mobile',
		'skype', 'website', 'facebook', 'twitter', 'linkedin', 'instagram', 'google_plus', 'youtube', 'pinterest', 'vimeo',
		'house', 'street', 'suburb', 'region', 'postal_code', 'state',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'password', 'remember_token',
    ];

    public static $statuses = [
  		0 => 'pending',
  		1 => 'published',
  		2 => 'rejected',
  		3 => 'deleted',
      4 => 'unpublished',
  	];
    
    public static $type = 'all';

	public static function getUserLabels() {
		return [
			'featured' => ['id' => 2, 'label' => __('Featured'), 'color' => 'orange'],
		];
	}

	public static $editRoute = 'user.profile.profile';
	public static $saveRoute = 'user.profile.save';

	/*public static $relativeModels = [
		'agency' => 'Agency',
		'agent' => 'Agent',
		'architect_firm' => 'ArchitectFirm',
		'architect' => 'Architect',
		'building_company' => 'BuildingCompany',
		'building_company_agent' => 'BuildingCompanyAgent',
		'professional' => 'Professional',
		'project_home_company' => 'ProjectHomeCompany',
		'project_home_company_agent' => 'ProjectHomeCompanyAgent',
		'property_management' => 'PropertyManagement',
		'vacation_home_company' => 'VacationHomeCompany',
	];*/

	public static function getRelativeData() {
		return [
			'agency' => __('Agencies'),
			'agent' => __('Agents'),
			'architect_firm' => __('Architect Firms'),
			'architect' => __('Architects'),
            'artist' => __('Artists'),
            'gallery' => __('Galleries'),
            'seller' => __('Sellers'),
            'wineseller' => __('Wine Sellers'),
            'furnitureseller' => __('Furniture Sellers'),
            'brand' => __('Brands'),
			'building_company' => __('Building Companies'),
			'building_company_agent' => __('Building Company Agents'),
            'design_company' => __('Design Companies'),
			'professional' => __('Professionals'),
			'project_home_company' => __('Project Home Companies'),
			'project_home_company_agent' => __('Project Home Company Agents'),
			'property_management' => __('Property Management'),
			'vacation_home_company' => __('Vacation Home Companies'),
		];
	}

	public static $agencies = [
		'agency',
		'architect_firm',
		'building_company',
		'project_home_company',
		'property_management',
		'vacation_home_company',
        'design_company',
	];

	public static function getAgencyAgents($role = '', $key = '') {
		$agencyAgents = [
			'agency' => ['role' => 'agent', 'title' => __('Agents'), 'search' => __('Search Agent')],
			'architect_firm' => ['role' => 'architect', 'title' => __('Architects'), 'search' => __('Search Architect')],
			'building_company' => ['role' => 'building_company_agent', 'title' => __('Building Company Agents'), 'search' => __('Search Building Company Agent')],
			'project_home_company' => ['role' => 'project_home_company_agent', 'title' => __('Project Home Company Agents'), 'search' => __('Search Project Home Company Agent')],
	];
		return empty($role) ? $agencyAgents : (empty($key) ? $agencyAgents[$role] : $agencyAgents[$role][$key]);
	}

	public static $phonesFileds = ['phone', 'mobile', 'fax_number', 'tax_number'];

	public static $socialFields = [
		'skype' => ['begins' => ['skype:'], 'http' => '', 'site' => 'skype:'],
		'website' => ['begins' => [], 'http' => 'http://', 'site' => ''],
		'facebook' => ['begins' => ['www', 'facebook'], 'http' => 'https://', 'site' => 'www.facebook.com/'],
		'twitter' => ['begins' => ['www', 'twitter'], 'http' => 'https://', 'site' => 'twitter.com/'],
		'linkedin' => ['begins' => ['www', 'linkedin'], 'http' => 'https://', 'site' => 'www.linkedin.com/in/'],
		'instagram' => ['begins' => ['www', 'instagram'], 'http' => 'https://', 'site' => 'www.instagram.com/'],
		'google_plus' => ['begins' => ['www', 'plus.google'], 'http' => 'https://', 'site' => 'plus.google.com/'],
		'youtube' => ['begins' => ['www', 'youtube'], 'http' => 'https://', 'site' => 'www.youtube.com/'],
		'pinterest' => ['begins' => ['www', 'pinterest'], 'http' => 'https://', 'site' => 'www.pinterest.com/'],
		'vimeo' => ['begins' => ['www', 'vimeo'], 'http' => 'http://', 'site' => 'vimeo.com/'],
	];
    
    public static $userEmailsToChange = [
        'engelvoelkers@medicaleer.com',
        'raywhite@medicaleer.com'
    ];

	public static $defPrefix = 'ud'; //alias for relation table with default language
	public static $langPrefix = 'ut'; //alias for relation table with current language

    public static $simpleSlugEntities = [
        'agent', 'agency', 'architect_firm', 'architect', 'artist', 'gallery', 'seller', 'wineseller', 'furnitureseller', 'brand', 'professional'
    ];
    
    public function isAdmin() {
      return $this->role()->where('name', 'administrator')->exists();
    }
    public function isRole($role) {
      return $this->role()->where('name', $role)->exists();
    }
    public function role()
    {
      return $this->belongsTo(Role::class);
    }

	public static function _isAgency($roleName) {
		return in_array($roleName, static::$agencies);
	}

	public static function _isAgencyWithAgents($roleName) {
		return array_key_exists($roleName, static::getAgencyAgents());
	}
    public function isArtist() {
        return $this->role()->where('name', 'artist')->exists();
    }
    public function isSeller() {
        return $this->role()->where('name', 'seller')->exists();
    }
    public function isWineseller() {
        return $this->role()->where('name', 'wineseller')->exists();
    }
    public function isFurnitureseller() {
        return $this->role()->where('name', 'furnitureseller')->exists();
    }
    public function isBrand() {
        return $this->role()->where('name', 'brand')->exists();
    }
    public function isGallery() {
        return $this->role()->where('name', 'gallery')->exists();
    }

	public static function _getAgencyType($roleName) {
		foreach(static::getAgencyAgents() as $acency => $agent) {
			if($agent['role'] == $roleName) {
				return $acency;
			}
		}
		return null;
	}

	public static function _isRelationExists($roleName) {
		return array_key_exists($roleName, static::getRelativeData());
	}

	public static function _getRelationModel($roleName) {
		return implode('', array_map('ucfirst', explode('_', $roleName)));
	}

	public static function _addRelation($query, $role, $select = 'users.*', $onlyName = false)
	{
		if(!static::_isRelationExists($role)) return $query;

		$defLang = BaseModel::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$model = static::_getRelationModel($role);
		$modelTable = $model::$tableName;
		$defPrefix = static::$defPrefix;
		$langPrefix = static::$langPrefix;

		$translatable = $onlyName ? ['company_name'] : $model::$translatable;
		$selectable = $onlyName ? ['company_name'] : $model::$selectable;
		
		$query->select(DB::raw($select.','.BaseModel::getLangFieldsList($selectable, $defPrefix, ($defLang == $langId ? [] : $translatable), $langPrefix)))
			->join($modelTable.' as '.$defPrefix, function ($join) use($defPrefix, $defLang){
           		$join->on($defPrefix.'.user_id', '=', 'users.id')
                	->where($defPrefix.'.lang_id', '=', $defLang);
        	});
        if($defLang != $langId) {
        	$query = BaseModel::replaceQuery($query, $translatable, $defPrefix, $langPrefix, '');

        	$query->leftJoin($modelTable.' as '.$langPrefix, function ($join) use($langPrefix, $langId){
           		$join->on($langPrefix.'.user_id', '=', 'users.id')
                	->where($langPrefix.'.lang_id', '=', $langId);
        	});
        }
        //dd($query->toSql());
        return $query;
	}

	public static function getUserRelation($id, $role)
	{
		if(!static::_isRelationExists($role)) return [];

		$model = static::_getRelationModel($role);
		$relation = $model::getEntity(['user_id' => $id]);
		return $relation ? $relation->toArray() : [];
	}

    public function authorizeRoles($roles)
    {
      if (is_array($roles)) {
          return $this->hasAnyRole($roles) || abort(401, 'This action is unauthorized');
      }
      return $this->hasRole($roles) || abort(401, 'This action is unauthorized');
    }

    public function hasAnyRole($roles)
    {
      return null !== $this->roles()->whereIn('name', $roles)->first();
    }

    public function hasRole($role)
    {
      return null !== $this->roles()->where('name', $role)->first();
    }

	public function getLanguageAttribute($value)
	{
		return explode(',', $value);
	}

	public static function createUser($data, $additional = [], $relationsData = [])
	{
		$data['password'] = Hash::make($data['password']);
		if(isset($data['map_address']) && !isset($data['address'])) {
			$data['address'] = $data['map_address'];
		}
		$user = static::create($data);

        if(!$user) return false;

		$id = $user->id;
		$role = isset($data['role_name']) ? $data['role_name'] : Role::find($user->role_id)->name;
		if(static::_isRelationExists($role)) {
        	$model = static::_getRelationModel($role);
        	$defLang = BaseModel::getDefaultLang();
        	if(sizeof($relationsData) > 0) {
        		foreach($relationsData as $langId => $langData) {
					$langData['user_id'] = $id;
					$langData['lang_id'] = $langId;
					$lang = new $model;
					$lang->fill($langData)->save();
				}
				if(!isset($relationsData[$defLang])) {
					$langData['lang_id'] = $defLang;
					$lang = new $model;
					$lang->fill($langData)->save();
				}
        	} else {
        		$relation = new $model;
        		$relation->fill(['user_id' => $id, 'lang_id' => $defLang])->save();
        	}
		}
        if(isset($additional['agency_id'])) {
            AgencyAgents::create(['agency_id' => $additional['agency_id'], 'agent_id' => $id, 'status' => 1]);
        }
        if($role == 'professional' && isset($data['professions']) && !empty($data['professions'])) {
        	Profession::saveProfessions(null, array_filter(explode(',', $data['professions'])), $id);
        }
        return $user;
    }

	/*
	 * Methods for Controllers
	 */
	public static function countUsersByRoles() {
	    $counters = [];
	    $roles = Role::where('id', '!=', 1)->get()->toArray();
	    if (!empty($roles)) {
	        foreach ($roles as $role) {
                $users = User::query();
                $counters[$role['name']] = [
                    'title' => $role['title'],
                    'count' => $users->where('users.role_id', $role['id'])->count(),
                    'published' => $users->where('users.role_id', $role['id'])->where('users.status', 1)->count()
                ];
            }
        }
        
        return $counters;
    }
	public static function countAgents() {
		$users = User::query();
		$users = $users->where('users.role_id', 2)->count();
		return $users;
	}

	public static function countAgencies() {
		$users = User::query();
		$users = $users->where('users.role_id', 3)->count();
		return $users;
	}

	public static function countPublishedAgents() {
		$users = User::query();
		$users = $users->where('users.role_id', 2)->where('users.status', 1)->count();
		return $users;
	}

	public static function countPublishedAgencies() {
		$users = User::query();
		$users = $users->where('users.role_id', 3)->where('users.status', 1)->count();
		return $users;
	}

	public static function getUserProfile($role, $status = null, $params = [])
	{
		//dd($params);
		$userId = Auth::user()->id;
		$user = static::_addRelation(static::where('users.id', '=', $userId), $role)->first();
		$userArr = static::_afterGet($user, $role, 2); //2- for see pending agency-agents links

		if(!empty($userArr)) {
			if($userArr['agency_agents'] == 'agency') {
				$userArr['new_agents'] = AgencyAgents::countNewAgents($userArr['id']);
			}
			switch(request()->route()->getName()) {
				case 'user.profile.agents':
						if($userArr['agency_agents'] == 'agency') {
							$userArr['agents'] = AgencyAgents::getAgentsForBackend($userArr['id'], $params);
						}
					break;
				case 'user.profile.quotesRequests':
		            $orderData = app(QuotesRequest::class)->getSortOrder($params);
		            $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'id';
		            $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';

		            $userArr['page_name'] = ucfirst($status) . ' Quotes Requests';

		            if($user->isAdmin()) {
		              $quotesRequests = QuotesRequest::query()->with(['user']);
		            } else {
		              $quotesRequests = QuotesRequest::where('quotes_requests.user_id', $userId);
		            }
		            if(!empty($params)) {
		              $quotesRequests = SearchHelper::applyCommonSearchParams($quotesRequests, $params);
		            }
		            if(!empty($status)) {
		              $statuses = QuotesRequest::getQuotesRequestsData('status');
		              $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
		              if(!empty($statusId)) {
		                $quotesRequests->where('quotes_requests.status', $statusId);
		              }
		            }
		            $pagination = $quotesRequests->orderBy($orderBy, $order)->paginate(BaseModel::$pagination);
		            $pagination->getCollection()->transform(function ($entity) {
		              $entity = QuotesRequest::_afterGet($entity);
		              $entityUser = !empty($entity['user']) ? $entity['user'] : null;
		              $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
		              $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
		              return $entity;
		            });

		            $userArr['quotesRequests'] = $pagination;
            		break;

				case 'user.profile.jobEntities':
						$orderData = app(JobEntity::class)->getSortOrder($params);
						$orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
						$order = !empty($orderData['order']) ? $orderData['order'] : 'asc';

						$userArr['page_name'] = ucfirst($status) . ' jobEntities';

						switch($status) {
							case 'favorite':
								$jobs = JobEntity::query()
									->select('job_entities.*')
									->join('job_entities_favorite as pf', 'job_entities.id', '=', 'pf.job_entity_id')
									->where('pf.user_id', $userId);
								break;
							default:
								if($user->isAdmin()) {
									$jobs = JobEntity::query()->with(['user']);
								} else {
									$jobs = JobEntity::where('job_entities.author', $userId);
								}
								if(!empty($params)) {
									$jobs = SearchHelper::applyCommonSearchParams($jobs, $params);
								}
								if(!empty($status)) {
									$statuses = JobEntity::getJobEntityData('status');
									$statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';

									if(!empty($statusId)) {
										$jobs->where('job_entities.status', $statusId);
									}
								}
								break;
						}
						$pagination = JobEntity::_addTranslation($jobs->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
						$pagination->getCollection()->transform(function ($entity) {
							$entity = JobEntity::_afterGet($entity);
							$entityUser = !empty($entity['user']) ? $entity['user'] : null;
							$entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
							$entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
							return $entity;
						});
						$userArr['job_entities_count'] =  ( !empty($userId) && !empty(JobEntity::getCountJobEntitiesByUser($userId)) ) ? JobEntity::getCountJobEntitiesByUser($userId) : 0;
						$userArr['jobEntities'] = $pagination;
						break;
				case 'user.profile.properties':
					$orderData = app(Property::class)->getSortOrder($params);
					$orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
					$order = !empty($orderData['order']) ? $orderData['order'] : 'asc';

					$userArr['page_name'] = ucfirst($status) . ' Properties';

					switch($status) {
						case 'favorite':
							$properties = Property::query()
								->select('properties.*')
								->join('properties_favorite as pf', 'properties.id', '=', 'pf.property_id')
								->where('pf.user_id', $userId);
							break;
						default:
							if($user->isAdmin()) {
								$properties = Property::query()->with(['user']);
							} else {
								$properties = Property::where('properties.author', $userId);
							}
							if(!empty($params)) {
								$properties = SearchHelper::applyCommonSearchParams($properties, $params);
							}
							if(!empty($status)) {
								$statuses = Property::getPropertyData('status');
								$statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';

								if(!empty($statusId)) {
									$properties->where('properties.status', $statusId);
								}
							}
							break;
					}
					$pagination = Property::_addTranslation($properties->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
					$pagination->getCollection()->transform(function ($entity) {
						$entity = Property::_afterGet($entity);
						$entityUser = !empty($entity['user']) ? $entity['user'] : null;
						$entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
						$entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
						return $entity;
					});
					$userArr['properties'] = $pagination;
					break;
                /*case 'user.profile.arts':
                    $orderData = app(Art::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    
                    $userArr['page_name'] = ucfirst($status) . ' Professional';
                    $params['search_type'] = 'art';
                    
                    switch($status) {
                        case 'favorite':
                            $arts = Art::query()
                                ->select('arts.*')
                                ->join('arts_favorite as pf', 'arts.id', '=', 'pf.art_id')
                                ->where('pf.user_id', $userId);
                            break;
                        default:
                            if($user->isAdmin()) {
                                $arts = Art::query()->with(['user']);
                            } else {
                                $arts = Art::where('arts.author', $userId);
                            }
                            if(!empty($params)) {
                                $arts = SearchHelper::applyCommonSearchParams($arts, $params);
                            }
                            if(!empty($status)) {
                                $statuses = Art::getArtData('status');
                                $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                                
                                if(!empty($statusId)) {
                                    $arts->where('arts.status', $statusId);
                                }
                            }
                            break;
                    }
                    $pagination = Art::_addTranslation($arts->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = Art::_afterGet($entity);
                        $entityUser = !empty($entity['user']) ? $entity['user'] : null;
                        $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
                        $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
                        return $entity;
                    });
                    $userArr['arts'] = $pagination;
                    break;*/
                case 'user.profile.products':
                    $orderData = app(Product::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    
                    $userArr['page_name'] = ucfirst($status) . ' Products';
                    $params['search_type'] = 'product';
                    
                    switch($status) {
                        case 'favorite':
                            $products = Product::query()
                                ->select('products.*')
                                ->join('products_favorite as pf', 'products.id', '=', 'pf.product_id')
                                ->where('pf.user_id', $userId);
                            break;
                        default:
                            if($user->isAdmin()) {
                                $products = Product::query()->with(['user']);
                            } else {
                                $products = Product::where('products.author', $userId);
                            }
                            if(!empty($params)) {
                                $products = SearchHelper::applyCommonSearchParams($products, $params);
                            }
                            if(!empty($status)) {
                                $statuses = Product::getProductData('status');
                                $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                                
                                if(!empty($statusId)) {
                                    $products->where('products.status', $statusId);
                                }
                            }
                            break;
                    }
                    $pagination = Product::_addTranslation($products->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = Product::_afterGet($entity);
                        $entityUser = !empty($entity['user']) ? $entity['user'] : null;
                        $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
                        $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
                        return $entity;
                    });
                    $userArr['products'] = $pagination;
                    break;
                case 'user.profile.wines':
                    $orderData = app(Wine::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    
                    $userArr['page_name'] = ucfirst($status) . ' Wines';
                    $params['search_type'] = 'wine';
                    
                    switch($status) {
                        case 'favorite':
                            $wines = Wine::query()
                                ->select('wines.*')
                                ->join('wines_favorite as pf', 'wines.id', '=', 'pf.wine_id')
                                ->where('pf.user_id', $userId);
                            break;
                        default:
                            if($user->isAdmin()) {
                                $wines = Wine::query()->with(['user']);
                            } else {
                                $wines = Wine::where('wines.author', $userId);
                            }
                            if(!empty($params)) {
                                $wines = SearchHelper::applyCommonSearchParams($wines, $params);
                            }
                            if(!empty($status)) {
                                $statuses = Wine::getWineData('status');
                                $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                                
                                if(!empty($statusId)) {
                                    $wines->where('wines.status', $statusId);
                                }
                            }
                            break;
                    }
                    $pagination = Wine::_addTranslation($wines->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = Wine::_afterGet($entity);
                        $entityUser = !empty($entity['user']) ? $entity['user'] : null;
                        $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
                        $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
                        return $entity;
                    });
                    $userArr['wines'] = $pagination;
                    break;
                case 'user.profile.news':
                    $orderData = app(News::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    
                    $userArr['page_name'] = ucfirst($status) . ' News';
                    $params['search_type'] = 'news';
                    
                    switch($status) {
                        case 'favorite':
                            $news = News::query()
                                ->select('news.*')
                                ->join('news_favorite as pf', 'news.id', '=', 'pf.news_id')
                                ->where('pf.user_id', $userId);
                            break;
                        default:
                            if($user->isAdmin()) {
                                $news = News::query()->with(['user']);
                            } else {
                                $news = News::where('news.author', $userId);
                            }
                            if(!empty($params)) {
                                $news = SearchHelper::applyCommonSearchParams($news, $params);
                                //dd($params);
                            }
                            if(!empty($status)) {
                                $statuses = News::getNewsData('status');
                                $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                                
                                if(!empty($statusId)) {
                                    $news->where('news.status', $statusId);
                                }
                            }
                            break;
                    }

                    $pagination = News::_addTranslation($news->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = News::_afterGet($entity);
                        $entityUser = !empty($entity['user']) ? $entity['user'] : null;
                        $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
                        $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
                        return $entity;
                    });
                    $userArr['news'] = $pagination;
                    //dd($userArr['news']);
                    break;
                case 'user.profile.furnitures':
                    $orderData = app(Furniture::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    
                    $userArr['page_name'] = ucfirst($status) . ' Furnitures';
                    $params['search_type'] = 'furniture';
                    
                    switch($status) {
                        case 'favorite':
                            $furnitures = Furniture::query()
                                ->select('furnitures.*')
                                ->join('furnitures_favorite as pf', 'furnitures.id', '=', 'pf.furniture_id')
                                ->where('pf.user_id', $userId);
                            break;
                        default:
                            if($user->isAdmin()) {
                                $furnitures = Furniture::query()->with(['user']);
                            } else {
                                $furnitures = Furniture::where('furnitures.author', $userId);
                            }
                            if(!empty($params)) {
                                $furnitures = SearchHelper::applyCommonSearchParams($furnitures, $params);
                            }
                            if(!empty($status)) {
                                $statuses = Furniture::getFurnitureData('status');
                                $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                                
                                if(!empty($statusId)) {
                                    $furnitures->where('furnitures.status', $statusId);
                                }
                            }
                            break;
                    }
                    $pagination = Furniture::_addTranslation($furnitures->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = Furniture::_afterGet($entity);
                        $entityUser = !empty($entity['user']) ? $entity['user'] : null;
                        $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
                        $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
                        return $entity;
                    });
                    $userArr['furnitures'] = $pagination;
                    break;
                case 'user.profile.goods':
                    $orderData = app(Good::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    
                    $userArr['page_name'] = ucfirst($status) . ' Goods';
                    $params['search_type'] = 'good';
                    
                    switch($status) {
                        case 'favorite':
                            $goods = Good::query()
                                ->select('goods.*')
                                ->join('goods_favorite as pf', 'goods.id', '=', 'pf.good_id')
                                ->where('pf.user_id', $userId);
                            break;
                        default:
                            if($user->isAdmin()) {
                                $goods = Good::query()->with(['user']);
                            } else {
                                $goods = Good::where('goods.author', $userId);
                            }
                            if(!empty($params)) {
                                $goods = SearchHelper::applyCommonSearchParams($goods, $params);
                            }
                            if(!empty($status)) {
                                $statuses = Good::getGoodData('status');
                                $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                                
                                if(!empty($statusId)) {
                                    $goods->where('goods.status', $statusId);
                                }
                            }
                            break;
                    }
                    $pagination = Good::_addTranslation($goods->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = Good::_afterGet($entity);
                        $entityUser = !empty($entity['user']) ? $entity['user'] : null;
                        $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
                        $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
                        return $entity;
                    });
                    $userArr['goods'] = $pagination;
                    break;
                case 'user.profile.designs':
                    $orderData = app(Design::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'title';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    
                    $userArr['page_name'] = ucfirst($status) . ' Architecture & Design Projects';
                    $params['search_type'] = 'design';
                    
                    switch($status) {
                        case 'favorite':
                            $designs = Design::query()
                                ->select('designs.*')
                                ->join('designs_favorite as pf', 'designs.id', '=', 'pf.design_id')
                                ->where('pf.user_id', $userId);
                            break;
                        default:
                            if($user->isAdmin()) {
                                $designs = Design::query()->with(['user']);
                            } else {
                                $designs = Design::where('designs.author', $userId);
                            }
                            if(!empty($params)) {
                                $designs = SearchHelper::applyCommonSearchParams($designs, $params);
                            }
                            if(!empty($status)) {
                                $statuses = Design::getDesignData('status');
                                $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                                
                                if(!empty($statusId)) {
                                    $designs->where('designs.status', $statusId);
                                }
                            }
                            break;
                    }
                    $pagination = Design::_addTranslation($designs->orderBy($orderBy, $order))->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = Design::_afterGet($entity);
                        $entityUser = !empty($entity['user']) ? $entity['user'] : null;
                        $entityUserType = !empty($entity['user']['type']) ? $entity['user']['type'] : null;
                        $entity['user'] = static::_afterGet($entityUser, $entityUserType, 1);
                        return $entity;
                    });
                    $userArr['designs'] = $pagination;
                    break;
                case 'user.profile.ads':
                    $orderData = app(Ads::class)->getSortOrder($params);
                    $orderBy = !empty($orderData['order_by']) ? $orderData['order_by'] : 'order';
                    $order = !empty($orderData['order']) ? $orderData['order'] : 'asc';
                    $userArr['page_name'] = ucfirst($status) . ' Ads';
                    
                    $ads = Ads::query();
                    
                    if(!empty($status)) {
                        $statuses = Ads::getAdsData('status');
                        $statusId = !empty($statuses[$status]) ? $statuses[$status]['id'] : '';
                        if(!empty($statusId)) {
                            $ads->where('ads.status', $statusId);
                        }
                    }
                    
                    if (!empty($params)) {
                        $ads = SearchHelper::applyAdsCommonSearchParams($ads, $params);
                    }
                    
                    $pagination = $ads->orderBy($orderBy, $order)->paginate(BaseModel::$pagination);
                    $pagination->getCollection()->transform(function ($entity) {
                        $entity = Ads::_afterGet($entity);
                        return $entity;
                    });
                    $userArr['ads'] = $pagination;
                    
                    break;
				default:
					break;
			}
			if($role == 'professional') {
				$userArr['professions'] = Profession::getProfessionsById($userArr['id']);
			}
			$userArr['projects'] = Projects::getUserProjects($userArr['id']);
		}
		//dd($userArr);
		return $userArr;
	}

	public static function setUserStatus($id, $status, $isAdmin = true)
	{
		$user = Auth::user();

		if($isAdmin && $user->role()->first()->name != 'administrator') {
			return redirect(route('user.profile.profile'));
		}

		$user = static::findOrFail($id);
		if(isset(static::$statuses[$status]) && $user['status'] != $status) {

			$user->fill(['status' => $status])->save();
			return $user;
		}
		return false;
	}

	public static function setUserLabel($id, $label)
	{
		if(!Auth::user()->isAdmin()) return redirect(url('/'));

		$user = static::find($id);

		$labels = static::getUserLabels();
		$labelId = isset($labels[$label]) ? $labels[$label]['id'] : null;

		if($user->label != $labelId) {
			$user->fill(['label' => $labelId])->save();
		}
		return true;
	}

	public static function saveUserProfile($request, $userImport = false) {
		if(!$userImport && config('app')['localization_type'] == 1) {
			CustomLaravelLocalization::setLocaleLL($request->getSession()->get('locale'));
		}
		$data = !$userImport ? $request->all() : $request;
		$id = (isset($data['id']) ? $data['id'] : null);
		
		$validator = Validator::make($data, [
			'email' => 'required|email|unique:users,email,' . (is_null($id) ? '' : $id),
			'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'house' => 'max:50',
            'street' => 'max:100',
            'suburb' => 'max:100',
            'region' => 'max:100',
            'postal_code' => 'max:255',
            'state' => 'max:255',
		]);

		if($validator->fails()) {
			return !$userImport ? redirect()->back()->withInput()->withErrors($validator) : $validator->errors();
		}
		
		if(!$userImport) {
			$user = Auth::user();
			$userId = $user->id;
			$my = true;
			$isAdmin = $user->isAdmin();

			if(!is_null($id) && $userId != $id) {
				if(!$isAdmin) {
					return redirect(route('user.profile.profile'));
				}
				$user = static::find($id);
				$my = false;
			}
			if(!$user) return redirect(url('/'));

			$role = $user->role()->first()->name;
			$data = Upload::attachUploads($data, $request, ['photo_id', 'header_media_id'], false);
			Profession::saveProfessions($request);

			if(static::_getAgencyType($role)) {
				AgencyAgents::saveAgency($id, isset($request->agency_name) && !empty($request->agency_name) ? $request->agency_id : null, $isAdmin ? 1 : 0);
			} elseif($isAdmin && static::_isAgencyWithAgents($role)) {
				AgencyAgents::saveAgents($id, $request->agents, 1);
			}

		} else {
			$user = !empty($data['new_id']) ? static::findOrNew($data['new_id']) : new static;
			unset($user['slug']);
			$role = $userImport;
		}

		if(static::_isRelationExists($role)) {
			$model = static::_getRelationModel($role);

			$langId = CustomLaravelLocalization::getLocaleCode();


			$relationData = [];
			foreach($model::$selectable as $field) {
				if($field == 'company_slug') continue;
				if(isset($data[$field]) && !is_null($data[$field])) {
					$relationData[$field] = $data[$field];
				}
			}
		}
		$user->fill($data)->save();
		$id = $user->id;

		if(isset($relationData)) {
			$relation = $model::where([['user_id', $id], ['lang_id', $langId]])->first();
			if(!$relation) {
				$relationData['user_id'] = $id;
				$relationData['lang_id'] = $langId;
				$relation = new $model;
			}
			$relation->fill($relationData)->save();
			ElasticSearchHelper::updateElasticEntity($user, $model::$tableName);
		}
		Upload::saveUploadedImages(isset($data['photos']) ? $data['photos'] : [], $id, 'users');
		if (Auth::user()->isAdmin()) {
			AddressKeyword::saveEntityKeywords(isset($data['keywords']) ? $data['keywords'] : [], $id, 'user');
		}

		if(isset($data['photos'])) {
			$captions = [];
			foreach($data['photos'] as $photo) {
				$captions[$photo] = (isset($data['photo_caption'.$photo]) ? $data['photo_caption'.$photo] : null);
			}
			Upload::setImageCaptions($id, $captions);
		}

		return !$userImport ? redirect($my ? route('user.profile.profile') : route('user.edit.admin', array('id' => $id))) : $user->toArray();
	}

	public static function getUserById($id)
	{
		$user = static::find($id);
		if(!$user) return redirect(url('/'));
		$role = $user->role()->first()->name;
		$user = static::_addRelation(static::where('users.id', '=', $id), $role)->first();
		$userArr = static::_afterGet($user, $role, 2);
		if (!$userArr) return redirect(route('user.profile.users'));
		if($role == 'professional') {
			$userArr['professions'] = Profession::getProfessionsById($userArr['id']);
		}
		
		$userArr['projects'] = Projects::getUserProjects($userArr['id']);
		return $userArr;
	}

	public static function deleteUser($id) {
		if(!isset($id)) redirect(route('home'));

		$user = Auth::user();
		$userId = $user->id;
		$userIsAdmin = $user->isAdmin();


		if($userId != $id) {
			if(!$userIsAdmin) {
				return redirect(route('user.profile.profile'));
			}
			$user = static::find($id);
		}

		if( !empty($user) && $userIsAdmin ) {
			$role = $user->role()->first()->name;

			if(static::_isRelationExists($role)) {
				$model = static::_getRelationModel($role);
				$model::where('user_id', $id)->delete();
			}
			$user->delete();
			AgencyAgents::where('agency_id', $id)->delete();
			AgencyAgents::where('agent_id', $id)->delete();
			SavedSearches::where('user_id', $id)->delete();
			PropertyFavorite::where('user_id', $id)->delete();
            App\Http\Models\Arts\ArtFavorite::where('user_id', $id)->delete();
            App\Http\Models\Products\ProductFavorite::where('user_id', $id)->delete();
            App\Http\Models\Wines\WineFavorite::where('user_id', $id)->delete();
            App\Http\Models\Furnitures\FurnitureFavorite::where('user_id', $id)->delete();
            App\Http\Models\Goods\GoodFavorite::where('user_id', $id)->delete();
            App\Http\Models\Designs\DesignFavorite::where('user_id', $id)->delete();
			ProfessionUser::where('user_id', $id)->delete();
		}

		if (!$userIsAdmin) {
			static::setUserStatus($id, 3, false);
			Auth::logout();
		}

		return redirect(route('user.profile.profile'));
	}

	public static function resetPassword($request) {
		$user = Auth::user();
		$userIsAdmin = $user->isAdmin();

		$userId = $request->get('id');

		if ($userId != $user->id) {
			if (!$userIsAdmin) {
				return ['message' => 'Your cannot change the password to another user.', 'errors_exist' => true];
			}
			$user = static::find($userId);
		}

		if(!empty($user)) {
			$password = $request->get('password');
			$passwordConfirmation = $request->get('password_confirmation');
			if (!$userIsAdmin) {
				$currentPassword = $request->get('current_password');
				if (!(Hash::check($currentPassword, $user->password))) {
					return ['message' => 'Your current password does not matches with the password you provided. Please try again.', 'errors_exist' => true];
				}
				if (strcmp($currentPassword, $password) == 0){
					return ['message' => 'New password cannot be same as your current password. Please choose a different password.', 'errors_exist' => true];
				}
			}
			if (strcmp($password, $passwordConfirmation) != 0){
				return ['message' => 'Your new password does not match the confirmation password. Please try again.', 'errors_exist' => true];
			}
			$validator = Validator::make($request->all(), [
				'current_password' => ($userIsAdmin ? '' : 'required'),
				'password' => 'required|confirmed|min:6',
			]);
			if($validator->fails()) {
				$messages = $validator->messages()->all();
				$message = '';

				foreach($messages as $m) {
					$message .= $m.' ';
				}
				return ['message' => $message, 'errors_exist' => true];
			}
			$user->password = bcrypt($password);
			$user->save();
			return ['message' => 'Password changed successfully!', 'errors_exist' => false];
		}
		return ['message' => '', 'errors_exist' => false];
	}

	public static function overrideKeywords($request) {
		$user = Auth::user();
		$userIsAdmin = $user->isAdmin();

		if (!$userIsAdmin) {
			return ['message' => 'Your cannot override keywords.', 'errors_exist' => true];
		}
		$userId = $request->get('user_id');

		$keywords = AddressKeyword::getEntityKeywords($userId, 'user');

		$products = Product::select('id')->where('author', $userId)->pluck('id');
		if ($products) {
            AddressKeyword::saveEntitiesKeywords($keywords, $products, 'product');
        }
        $wines = Wine::select('id')->where('author', $userId)->pluck('id');
		if ($wines) {
            AddressKeyword::saveEntitiesKeywords($keywords, $wines, 'wine');
        }
        $furnitures = Furniture::select('id')->where('author', $userId)->pluck('id');
		if ($furnitures) {
            AddressKeyword::saveEntitiesKeywords($keywords, $furnitures, 'furniture');
        }
        $goods = Good::select('id')->where('author', $userId)->pluck('id');
		if ($goods) {
            AddressKeyword::saveEntitiesKeywords($keywords, $goods, 'good');
        }
        $arts = Art::select('id')->where('author', $userId)->pluck('id');
		if ($arts) {
            AddressKeyword::saveEntitiesKeywords($keywords, $arts, 'art');
        }
        $designs = Design::select('id')->where('author', $userId)->pluck('id');
		if ($designs) {
            AddressKeyword::saveEntitiesKeywords($keywords, $designs, 'design');
        }
        $news = News::select('id')->where('author', $userId)->pluck('id');
		if ($news) {
            SimpleKeyword::saveEntitiesKeywords($keywords, $news, 'news');
        }
        $cnt = count($products) + count($wines) + count($furnitures) + count($goods) + count($arts) + count($designs) + count($news);

		return ['message' => 'Keywords overrided successfully (entities: ' . $cnt . ')!', 'errors_exist' => false];
	}

	public static function getAllUsersList($params)
	{
		$users = User::select(['users.*', 'roles.name as role_name', 'roles.title as role_title'])
			->join('roles', 'users.role_id', '=', 'roles.id');
		if(isset($params['status']) && !is_null($params['status'])) {
			$status = array_search($params['status'], static::$statuses);
			if($status !== false) {
				$users->where('users.status', '=', $status);
			}
		}
		if(isset($params['role']) && !is_null($params['role'])) {
			$users->where('roles.name', '=', $params['role']);
			if ($params['role'] === 'professional') {
				$users->join('professions_users', 'users.id', '=', 'user_id');
				if (!empty($params['profession_id'])) {
					$users->where('professions_users.profession_id', '=', $params['profession_id']);
				}
			}
		}
		if(isset($params['name']) && !is_null($params['name'])) {
			$isID = false;
			$keyword = $params['name'];
			if(substr(strtoupper($keyword), 0, 3) == 'ID:') {
				$id = trim(substr($keyword, 3));
				if(ctype_digit($id)) {
					$users->where('users.id', '=', $id);
					$isID = true;
				}
			}
			if(!$isID) {
				$name = '%'.$keyword.'%';
				$users->where(function ($query) use ($name) {
					$query->where('users.first_name', 'ilike', $name)
						->orWhere('users.last_name', 'ilike', $name)
						->orWhere('users.name', 'ilike', $name)
						->orWhere(DB::raw('CONCAT(users.first_name,\' \',users.last_name)'), 'ilike', $name);
					
					if (Auth::user()->isAdmin()) {
                        $query->orWhere('users.email', 'ilike', $name);
                    }
					});
			}
		}
		$orderBy = 'id';
		
		$pagination = $users->orderBy($orderBy, 'desc')->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity, $entity->role_name, 1, true); //1 - for see only Agency
		});
		return $pagination;
	}

	public static function getAllUsers($roleName, $params = [], $withPagination = true)
	{
		$role = Role::where('name', $roleName)->first();
		//dd($roleName,$params, $role);
		if(!$role) return [];

		$users = User::where([['users.role_id', $role->id], ['users.status', 1]]);
		if(!empty($params)) {
			$users = SearchHelper::applyCommonSearchParams($users, $params);
		}

		$orderData = isset($params['order_by']) && !empty($params['order_by']) ? $params['order_by'] : null;
		switch($orderData) {
			case 'a_date':
				$users->orderBy('users.updated_at', 'asc');
				break;
			case 'd_date':
				$users->orderBy('users.updated_at', 'desc');
				break;
			case 'name':
				if(static::_isAgency($roleName)) {
					$defLang = BaseModel::getDefaultLang();
					$langId = CustomLaravelLocalization::getLocaleCode();
					$users->orderBy(DB::raw('COALESCE('.($langId != $defLang ? static::$langPrefix.'.company_name,' : '').static::$defPrefix.'.company_name,CONCAT(users.first_name, \' \', users.last_name))'));
					//$users->orderBy(DB::raw('case when "company_name" is null then concat(users.first_name, \' \', users.last_name) else "company_name" end'));
				} else {
					$users->orderBy('users.first_name', 'asc');
					$users->orderBy('users.last_name', 'asc');
				}
				break;
			default:
				$users->orderBy('users.label', 'asc');
				$users->orderBy('users.updated_at', 'desc');
				break;
		}
		if(!$withPagination) {
			$users->where('email', 'not like', 'info@medicaleer%')->limit(100);
			return static::_addRelation($users, $roleName)->get();
		}
		$pagination = static::_addRelation($users, $roleName)->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($entity) use($roleName) {
			return static::_afterGet($entity, $roleName, 1);
		});
		return $pagination;
	}

	public static function searchUsers($roleName, $keyword, $agency = null)
	{
        $user = Auth::user();
		$myAgents = false;
		$manyRoles = [];
        $roleName = 'all' == $roleName ? null : $roleName;
		if(is_null($roleName)) {
			if(!$user->isAdmin()) {
				return [];
			}
			$role = null;
		} else {
			if($roleName == 'my_agents') {
				$myAgents = true;
				$myRole = $user->role()->first()->name;
				if(static::_isAgencyWithAgents($myRole)) {
					$roleName = static::getAgencyAgents($myRole, 'role');
				}
				$agency = $user->id;
			}

			if (is_array($roleName)) {
                $role = Role::whereIn('name', $roleName)->get();
                foreach ($role as $item) {
                    $manyRoles[] = $item->id;
                }
            } else {
                $role = Role::where('name', $roleName)->first();
            }
			if(!$role) return [];
		}

		$users = User::query();
		$isAgency = false;
		if($role) {
			$users->where('users.status', 1);
			if (!empty($manyRoles)) {
			    $roles = $user->isAdmin() || $myAgents ? array_merge([$user->role_id], $manyRoles) : $manyRoles;
                $users->whereIn('users.role_id', $roles);
            } elseif ($user->isAdmin() || $myAgents) {
				$users->whereIn('users.role_id', [$role->id, $user->role_id]);
			} else {
				$users->where('users.role_id', $role->id);
			}
            if (is_array($roleName)) {
			    foreach ($roleName as $item) {
                    $isAgency = static::_isAgency($item);
                    if ($isAgency) break;
                }
            } else {
                $isAgency = static::_isAgency($roleName);
            }
		}
		if(ctype_digit($keyword)) {
			$users->where('users.id', $keyword);
		} else {
			if(strlen($keyword) < 4) return [];
			$name = '%'.$keyword.'%';
			$users->where(function ($query) use ($name, $isAgency) {
				$query->where('users.first_name', 'ilike', $name)
					->orWhere('users.last_name', 'ilike', $name)
					->orWhere(DB::raw('CONCAT(users.first_name,\' \',users.last_name)'), 'ilike', $name)
                    ->orWhere('users.name', 'ilike', $name);
				if($isAgency) {
					$query->orWhere('company_name', 'ilike', $name);
				}
			});
		}
		$select = 'users.id, users.first_name, users.last_name';

		if($isAgency) {
		    is_array($roleName) && $roleName = array_shift($roleName);
			$users = static::_addRelation($users, $roleName, $select, true);
		} else {
			$users->select(DB::raw($select));
			if($agency) {
				$users->leftJoin('agency_agents as a', function ($join) use($agency, $myAgents) {
					$join->on('a.agent_id', '=', 'users.id')
						->where('a.status', 1)
						->where('a.agency_id', ($myAgents ? '=' : '!='), $agency);
				});
				if($myAgents) {
					$users->where(function($query) use($user) {
    					$query->whereNotNull('a.id')->orWhere('users.id', $user->id);
					});
				} else {
					$users->whereNull('a.id');
				}
			}
		}
		$users = $users->limit(15)->get();
		$results = [];
		if($users) {
			foreach ($users as $user) {
				$results[] = ['id' => $user->id, 'name' => ($isAgency && $user->company_name ? $user->company_name : $user->first_name.' '.$user->last_name).' (ID '.$user->id.')'];
			}
		}

		return $results;
	}

	public static function getUserMail($slug) {
		if (!empty($slug)) {
			$user = static::where('users.slug', $slug)->get()->first();
			if ($user) {
				return [
					'user_email' => $user->email,
					'user_first_name' => $user->first_name,
					'user_last_name' => $user->last_name
				];
			}
		}
		return false;
	}

	public static function getUserBySlug($role, $slug, $request = null)
	{
		$user = static::query();
        if ($role == 'all') {
        	
            $tmpUser = $user->where('users.slug', $slug)->get()->first();
            if (!$tmpUser) {
                $prefix = static::$defPrefix;
                foreach (static::$agencies as $openingRole) {
                    $someTmpUser = static::query()->where($prefix.'.company_slug', $slug)
                        ->orWhere(function($query) use($prefix, $slug) {
                            $query->whereNull($prefix.'.company_slug')->where('users.slug', $slug);
                        });;
                    $someTmpUser = static::_addRelation($someTmpUser, $openingRole)->first();
                    if ($someTmpUser) {
                        $tmpUser = $someTmpUser;
                        break;
                    } else {
                        $someTmpUser = null;
                    }
                }
            }
            
            if ($tmpUser) {
                $tmpRole = Role::query()->where('id', '=', $tmpUser->role_id)
                    ->get(['name'])->first()->toArray()['name'];
                if (in_array($tmpRole, static::$simpleSlugEntities)) {
                    $role = $tmpRole;
                }
            }
        }
        
        if (!isset($someTmpUser) || !$someTmpUser) {
            if (static::_isAgency($role)) {
                $prefix = static::$defPrefix;
                $user = $user->where('users.slug', $slug);
            } else {
                $user = $user->where('users.slug', $slug);
            }
           
            $user = static::_addRelation($user, $role)->first();
        } else {
            $user = $tmpUser;
        }
        //dd($user, $role);
		if(!$user || $user->status != 1) return abort('404'); //return redirect(route('home'));

		$userRaw = $user->toArray();

		$userArr = static::_afterGet($userRaw, $role, true);
        $userArr['role'] = $role;
        
		$properties = Property::_addTranslation(Property::where([['author', $user->id], ['status', 1]]))->count();
		if(!empty($properties)) {
			$userRaw['type'] = $role;
            
            $pagination = Property::_addTranslation(Property::where([['author', $user->id], ['status', 1]]))->paginate(50);
            $pagination->getCollection()->transform(function ($entity) use ($userRaw,$userArr) {
                $entity = Property::_afterGet($entity);
                $entity['user'] = $userRaw;
                if (empty($entity['uploadsList'])) {
                    $entity['uploadsList'][] = [
                        'id' => 0,
                        'name' => Property::$defaultImage,
                        'type' => 1
                    ];
                }
                return $entity;
            });
            
            $userArr['properties'] = $pagination;
		}
		if($role == 'professional') {
			$userArr['professions'] = Profession::getProfessionsById($userArr['id']);
		}

        /*if(in_array($role, ['artist', 'gallery'])) {
            $userArr['arts'] = Art::getAll(['author' => $userArr['id']], ['user']);
        }*/
        if($role == 'seller') {
        	$orderBy = Good::getSortOrder($request);
            $userArr['goods'] = Good::getAll(['author' => $userArr['id']], ['user'], $orderBy['order_by'], $orderBy['order']);
        }
        if($role == 'wineseller') {
        	$orderBy = Wine::getSortOrder($request);
            $userArr['wines'] = Wine::getAll(['author' => $userArr['id']], ['user'], $orderBy['order_by'], $orderBy['order']);
        }
        if($role == 'furnitureseller') {
        	$orderBy = Furniture::getSortOrder($request);
            $userArr['furnitures'] = Furniture::getAll(['author' => $userArr['id']], ['user'], $orderBy['order_by'], $orderBy['order']);
        }
        if($role == 'brand') {
        	$orderBy = Product::getSortOrder($request);
            $userArr['products'] = Product::getAll(['author' => $userArr['id']], ['user'], $orderBy['order_by'], $orderBy['order']);
        }
        $userArr['designs'] = Design::getAll(['author' => $userArr['id']], ['user']);
		$userArr['projects'] = Projects::getUserProjects($userArr['id']);
		//dd($userArr, $role);		
		return $userArr;
	}

	public static function _afterGet($user, $role = null, $setAA = null, $relation = false) {
		//$setAA:
		// true - get agents and agency (links status = 1)
		// 1 - get only agency for agents (links status = 1)
		// 2 - get agency and agents (links status = 0, 1)
		$userArr = empty($user) ? [] : (!is_array($user) ? $user->toArray() : $user);

		if(!empty($userArr)) {
			$userArr['country_id'] = !empty($userArr['country']) ? Country::getCountryIdByName($userArr['country']) : 0;
			$userArr['photoImage'] = !empty($userArr['photo']) ? Upload::getUploadById($userArr['photo']) : [];
			$userArr['headerMedia'] = !empty($userArr['header_media']) ? Upload::getUploadById($userArr['header_media']) : [];

			if(!empty($role)) {
				if($relation) {
					$userArr = array_merge($userArr, static::getUserRelation($userArr['id'], $role));
				}
				$userArr['type'] = $role;

				if ($role === 'professional') {
					$professionId = Profession::getProfessionByUserId($userArr['id']);
					if (!empty($professionId)) {
						$professionDefImg = Profession::getProfessionDefaultImg($professionId);
						if ( !empty($professionDefImg) ) {
							$professionDefImg['img_logo'] = ( !empty($professionDefImg['img_logo']) ) ? Upload::getUploadById( $professionDefImg['img_logo'] ) : [];
							$userArr['img_logo'] = ( !empty($professionDefImg['img_logo']) ) ? '/uploads/'.$professionDefImg['img_logo']['name'] : [];

							$professionDefImg['img_background'] = ( !empty($professionDefImg['img_background']) ) ? Upload::getUploadById( $professionDefImg['img_background'] ) : [];
							$userArr['img_background'] = ( !empty($professionDefImg['img_background']) ) ? '/uploads/'.$professionDefImg['img_background']['name'] : [];
						}
					}
				}

				$userArr['img_logo'] = !empty($userArr['img_logo']) ? $userArr['img_logo'] : '/images/logo-profilepic.jpg';
				$userArr['img_background'] = !empty($userArr['img_background']) ? $userArr['img_background'] : '/images/page-media-header.png';

				$userArr['img_logo'] = ( empty($userArr['photoImage']['name']) ) ? $userArr['img_logo'] : url('/uploads'). '/'. $userArr['photoImage']['name'];
				$userArr['img_background'] = ( empty($userArr['headerMedia']['name']) ) ? $userArr['img_background'] : url('/uploads'). '/'. $userArr['headerMedia']['name'];

			}
			$userArr['is_agency'] = static::_isAgency($role);
			$userArr['agency_agents'] = static::_isAgencyWithAgents($role) ? 'agency' : (static::_getAgencyType($role) ? 'agent' : null);
			
			if($setAA) {
				if($userArr['agency_agents'] == 'agency' && $setAA !== 1) {
					$userArr['agent_type'] = static::getAgencyAgents($role, 'role');
					$userArr['agents'] = AgencyAgents::getAgentsForFrontend($userArr['id'], $userArr['agent_type']);
				} else if($userArr['agency_agents'] == 'agent') {

					$agencyType = static::_getAgencyType($role);
					$userArr['agency_type'] = $agencyType;
					$userArr['agency'] = AgencyAgents::getAgency($userArr['id'], $agencyType, ($setAA === 2));
				}
			}
			$userArr['created_format'] = date('d.m.Y', strtotime($userArr['created_at']));

			if(is_null(Auth::user())) {
				$userArr = static::hidePhones($userArr);
			}
			$userArr = static::setCorrectLinks($userArr);
			$userArr['uploadsList'] = Upload::getUploadedImages($userArr['id'], 'users');
            $userArr['uploadsTypes'] = [];
            foreach($userArr['uploadsList'] as $key => $upload) {
                if(!in_array($upload['type'], $userArr['uploadsTypes'])) {
                    array_push($userArr['uploadsTypes'], $upload['type']);
                }
                if (!empty($upload['name'])) {
                    $userArr['uploadsList'][$key]['name'] = url('/uploads'). '/'. $upload['name'];
                }
            }
            $userArr['keywords'] = AddressKeyword::getEntityKeywords($userArr['id'], 'user');
		}
		return $userArr;
	}

	public static function hidePhones($userArr) {
		foreach(static::$phonesFileds as $i => $field) {
			if(isset($userArr[$field]) && !is_null($userArr[$field])) {
				$userArr[$field] = substr($userArr[$field], 0, 5) . '*******';
			}
		}
		return $userArr;
	}

	public static function setCorrectLinks($userArr) {
		foreach(static::$socialFields as $field => $data) {
			if(isset($userArr[$field]) && substr($userArr[$field], 0, 4) != 'http') {
				$link = $userArr[$field];
				$correct = false;
				foreach($data['begins'] as $i => $begin) {
					if(substr($link, 0, strlen($begin)) == $begin) {
						$userArr[$field] = $data['http'] . $userArr[$field];
						$correct = true;
						break;
					}
				}
				if(!$correct) {
					$userArr[$field] = $data['http'] . $data['site'] . $userArr[$field];
				}
			}
		}
		return $userArr;
	}

	public static function getFields($role) {
		$fields = static::_getFieldsList($role);

		if($role == 'professional') {
			$professions = Profession::getEntities();
			foreach($professions as $v) {
				$fields['partials']['professions']['options'][$v['profession_id']] = $v['name'];
			}
		}

		$languages = CustomLaravelLocalization::getSupportedLocales();
		foreach($languages as $l) {
			$fields['partials']['language']['options'][$l['code']] = $l['name'];
		}

		$agencyType = static::_getAgencyType($role);
		if(!is_null($agencyType)) {
			$fields['agency']['label'] = Role::where('name', $agencyType)->get()->first()->title;
		} else {
			$agents = static::getAgencyAgents();
			if(isset($agents[$role])) {
				$fields['agents']['label'] = $agents[$role]['title'];
				$fields['agents']['search'] = $agents[$role]['search'];
			}
		}
		//dd($fields);
		return $fields;
	}

	public static function _getFieldsList($role) {
		$fields = [
			'user' => [
				'name' => [
					'type' => 'text',
					'label' => __('Username'),
					'value' => ['user', 'name'],
					'disabled' => 1,
				],
				'email' => [
					'type' => 'text',
					'label' => __('Email'),
					'value' => ['user', 'email'],
				],
				'first_name' => [
					'type' => 'text',
					'label' => __('First Name'),
					'value' => ['user', 'first_name'],
				],
				'last_name' => [
					'type' => 'text',
					'label' => __('Last Name'),
					'value' => ['user', 'last_name'],
				],
			],
			'contacts' => [
				'tax_number' => [
					'type' => 'text',
					'label' => __('Tax Number'),
					'value' => ['user', 'tax_number'],
				],
				'phone' => [
					'type' => 'text',
					'label' => __('Phone'),
					'value' => ['user', 'phone'],
				],
				'fax_number' => [
					'type' => 'text',
					'label' => __('Fax Number'),
					'value' => ['user', 'fax_number'],
				],
				'mobile' => [
					'type' => 'text',
					'label' => __('Mobile'),
					'value' => ['user', 'mobile'],
				],
			],
			'address' => [
				'address' => [
					'index' => 'address',
					'type' => 'text',
					'label' => __('Address'),
					'value' => ['user', 'address'],
				],
				'map_address' => [
					'index' => 'map_address',
					'type' => 'map',
					'label' => __('Location'),
					'value' => [
						'map' => ['user', 'map_address'],
						'lat' => ['user', 'lat'],
						'lng' => ['user', 'lng'],
					],
					'placeholder' => 'London, UK',
				],
			],
			'social' => [
				'skype' => [
					'type' => 'text',
					'label' => __('Skype'),
					'icon' => 'fa fa-skype',
					'value' => ['user', 'skype'],
				],
				'website' => [
					'type' => 'text',
					'label' => __('Website URL'),
					'icon' => 'fa fa-globe',
					'value' => ['user', 'website'],
				],
				'facebook' => [
					'type' => 'text',
					'label' => __('Facebook URL'),
					'icon' => 'fa fa-facebook-square',
					'value' => ['user', 'facebook'],
				],
				'twitter' => [
					'type' => 'text',
					'label' => __('Twitter URL'),
					'icon' => 'fa fa-twitter-square',
					'value' => ['user', 'twitter'],
				],
				'linkedin' => [
					'type' => 'text',
					'label' => __('Linkedin URL'),
					'icon' => 'fa fa-linkedin-square',
					'value' => ['user', 'linkedin'],
				],
				'instagram' => [
					'type' => 'text',
					'label' => __('Instagram URL'),
					'icon' => 'fa fa-instagram',
					'value' => ['user', 'instagram'],
				],
				'google_plus' => [
					'type' => 'text',
					'label' => __('Google Plus URL'),
					'icon' => 'fa fa-google-plus-square',
					'value' => ['user', 'google_plus'],
				],
				'youtube' => [
					'type' => 'text',
					'label' => __('Youtube URL'),
					'icon' => 'fa fa-youtube-square',
					'value' => ['user', 'youtube'],
				],
				'pinterest' => [
					'type' => 'text',
					'label' => __('Pinterest URL'),
					'icon' => 'fa fa-pinterest-square',
					'value' => ['user', 'pinterest'],
				],
				'vimeo' => [
					'type' => 'text',
					'label' => __('Vimeo URL'),
					'icon' => 'fa fa-vimeo-square',
					'value' => ['user', 'vimeo'],
				],
			],
			'partials' => [
				'language' => [
					'index' => 'language',
					'type' => 'multiselectbox',
					'label' => __('Language'),
					'options' => [],
					'value' => ['user', 'language'],
					'placeholder' => 'English, Spanish, French',
				],
				'professions' => [
					'index' => 'professions[]',
					'type' => 'multiselectbox',
					'label' => __('Professions'),
					'options' => [],
					'value' => ['user', 'professions'],
				],
				'header_media' => [
					'index' => 'header_media',
					'type' => 'file',
					'value' => ['user', 'header_media'],
				],
				'photo' => [
					'index' => 'photo',
					'type' => 'file',
					'value' => ['user', 'photo'],
				],
			],
		];
		
		if (Auth::user()->isAdmin()) {
            $fields['user']['slug'] = [
                'type' => 'text',
                'label' => __('Slug'),
                'value' => ['user', 'slug']
            ];
        }

		if(static::_isRelationExists($role)) {
			$model = static::_getRelationModel($role);
			$fields = array_merge_recursive($fields, $model::_getFieldsList());
		}
		return $fields;
	}
}

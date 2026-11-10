<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Validator;
use Feature;
use Profession;
use User;
use Response;
use Projects;
use SavedSearches;
use Email;
use Role;
use AgencyAgents;
use Professional;
use Gallery;
use JobEntity;
use Property;
use JobCategory;
use Franchise;
use Ads;
use Art;
use DbImporter;
use AdUser;
use Cookie;
use Country;
use Product;
use ProductCategory;
use Wine;
use WineCategory;
use Furniture;
use FurnitureCategory;
use Good;
use GoodCategory;
use ArtCategory;
use Design;
use DesignCategory;
use PropertyCategory;
use QuotesRequest;
use News;

class UserController extends Controller
{
	public $model;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['getAllUsers', 'getUserBySlug', 'applyConsents', 'getAllUsersJson', 'getUserBySlugJson']);
    }

    public function getUserProfile(Request $request, $param = null)
    {
		$model = $this->getModel();
        $data = $this->_presetData();
        $params = static::getParamsFromRequest($request);
		$data = array_merge($data, [
			'user' => $model::getUserProfile($data['user_role'], $param, $params),
		]);

		switch(request()->route()->getName()) {
            case 'user.profile.ads':
                $data['filter'] = $params;
                $data['status'] = Ads::getAdsData('status');
                $data['order_by'] = Ads::getAdsData('order_by');
                break;
			case 'user.profile.properties':
				$data['filter'] = $params;
				$data['property_labels'] = Property::getPropertyData('label');
				$data['property_status'] = Property::getPropertyData('status');
				$data['counters'] = Property::countProperties();
				$data['property_categories_front'] = PropertyCategory::getAllList();
				break;
			case 'user.profile.propertyCategories':
                $data['propertyCategories'] = PropertyCategory::getEntities($params, 'name', true);
                $data['filter'] = $params;
                $data['counters'] = PropertyCategory::countCategories();
                break;
			case 'user.profile.arts':
			 	$data['filter'] = $params;
			 	$data['art_labels'] = Art::getArtData('label');
                $data['art_status'] = Art::getArtData('status');
                $data['counters'] = Art::countArts();
                $data['art_categories_front'] = ArtCategory::getAllList();
			 	break;
            case 'user.profile.artCategories':
                $data['artCategories'] = ArtCategory::getEntities($params, 'name', true);
                $data['filter'] = $params;
                $data['counters'] = ArtCategory::countCategories();
                break;
            case 'user.profile.products':
                $data['filter'] = $params;
                $data['product_labels'] = Product::getProductData('label');
                $data['product_status'] = Product::getProductData('status');
                $data['counters'] = Product::countProducts();
                $data['product_categories_front'] = ProductCategory::getAllList();
                break;
            case 'user.profile.productCategories':
                $data['productCategories'] = ProductCategory::getEntities($params, 'name', true);
                $data['filter'] = $params;
                $data['counters'] = ProductCategory::countCategories();
                break;
            case 'user.profile.wines':
                $data['filter'] = $params;
                $data['wine_labels'] = Wine::getWineData('label');
                $data['wine_status'] = Wine::getWineData('status');
                $data['counters'] = Wine::countWines();
                $data['wine_categories_front'] = WineCategory::getAllList();
                break;
            case 'user.profile.news':
                $data['filter'] = $params;
                $data['news_labels'] = News::getNewsData('label');
                $data['news_status'] = News::getNewsData('status');
                $data['counters'] = News::countNews();
                break;
            case 'user.profile.wineCategories':
                $data['wineCategories'] = WineCategory::getEntities($params, 'name', true);
                $data['filter'] = $params;
                $data['counters'] = WineCategory::countCategories();
                break;
            case 'user.profile.furnitures':
                $data['filter'] = $params;
                $data['furniture_labels'] = Furniture::getFurnitureData('label');
                $data['furniture_status'] = Furniture::getFurnitureData('status');
                $data['counters'] = Furniture::countFurnitures();
                $data['furniture_categories_front'] = FurnitureCategory::getAllList();
                break;
            case 'user.profile.furnitureCategories':
                $data['furnitureCategories'] = FurnitureCategory::getEntities($params, 'name', true);
                $data['filter'] = $params;
                $data['counters'] = FurnitureCategory::countCategories();
                break;
            case 'user.profile.goods':
                $data['filter'] = $params;
                $data['good_labels'] = Good::getGoodData('label');
                $data['good_status'] = Good::getGoodData('status');
                $data['counters'] = Good::countGoods();
                $data['good_categories_front'] = GoodCategory::getAllList();
                break;
            case 'user.profile.goodCategories':
                $data['goodCategories'] = GoodCategory::getEntities($params, 'name', true);
                $data['filter'] = $params;
                $data['counters'] = GoodCategory::countCategories();
                break;
            case 'user.profile.designs':
                $data['filter'] = $params;
                $data['design_labels'] = Design::getDesignData('label');
                $data['design_status'] = Design::getDesignData('status');
                $data['counters'] = Design::countDesigns();
                $data['design_categories_front'] = DesignCategory::getAllList();
                break;
            case 'user.profile.designCategories':
                $data['designCategories'] = DesignCategory::getEntities($params, 'name', true);
                $data['filter'] = $params;
                $data['counters'] = DesignCategory::countCategories();
                //$data['design_categories_front'] = DesignCategory::getAllList();
                break;
            case 'user.profile.quotesRequests':
				$data['filter'] = $params;
				$data['quotes_request_status'] = QuotesRequest::getQuotesRequestsData('status');
				break;
			case 'user.profile.jobEntities':
				$data['filter'] = $params;
				$data['jobEntity_labels'] = JobEntity::getJobEntityData('label');
				$data['filter']['settings'] = JobEntity::getJobSettings();
				break;
			case 'user.profile.jobCategories':
				$data['jobCategories'] = JobCategory::getEntities($params, 'name', true);
				$data['filter'] = $params;
				break;
			// case 'user.profile.franchises':
			// 	$data['filter'] = $params;
			// 	$data['franchise_labels'] = Franchise::getFranchiseData('label');
			// 	break;
			case 'user.profile.profile':
				$data['fields'] = $model::getFields($data['user_role']);
				break;
			case 'user.profile.agents':
				$statuses = [];
				foreach(AgencyAgents::$statuses as $i => $name) {
					$statuses[$i] = ['name' => $name, 'label' => ucfirst($name)];
				}
				$data['agent_statuses'] = $statuses;
				$data['filter'] = $params;
				break;
			case 'user.profile.features':
				$data['features'] = Feature::getEntities($params, 'name', true);
				$data['filter'] = $params;
				$data['counters'] = Feature::countFeatures();
				break;
			case 'user.profile.professions':
				$data['professions'] = Profession::getEntities($params, 'name', true);
				$data['filter'] = $params;
				break;
			case 'user.profile.users':
				$params['status'] = $param;
				$data['users'] = $model::getAllUsersList($params);
				$statuses = [];
				foreach($model::$statuses as $i => $name) {
					$statuses[$i] = ['name' => $name, 'label' => ucfirst($name), 'id' => $i];
				}
				$data['user_statuses'] = $statuses;
                $data['counters'] = User::countUsersByRoles();
				$data['user_labels'] = User::getUserLabels();
				$data['user_roles'] = Role::where('name', '!=', 'administrator')->get()->toArray();
				$data['profession_categories'] = Profession::select('profession_id', 'name')->get()->toArray();
				$data['filter'] = $params;
				break;
			case 'user.edit.admin':
				$user = $model::getUserById($param);
				if(!$user || !is_array($user)) return redirect()->route('home');
				$data['user'] = $user;
				$data['fields'] = $model::getFields($user['type']);
				$data['not_me'] = 1;
				break;
			case 'user.profile.saved_searches':
				$data['user']['saved_searches'] = app('SavedSearches')->getUserSearches();
				$data['counter'] = count($data['user']['saved_searches']);
				break;
			default:
				break;
		}
    
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->iso2] = $country->id;
            $data['countries_names'][$country->name] = $country->id;
        }
        //dd($data);
		
		return $this->showData($data);
    }

	public function saveUserProfile(Request $request) {
		$model = $this->getModel();
		return $model::saveUserProfile($request);
	}

	public function deleteUserProfile(Request $request) {
		$model = $this->getModel();
		return $model::deleteUser($request->id);
	}

	public function resetUserPassword(Request $request) {
		$model = $this->getModel();
		$responce = $model::resetPassword($request);
		return Response::json($responce, 200);
	}

	public function overrideKeywords(Request $request) {
		$responce = User::overrideKeywords($request);
		return Response::json($responce, 200);
	}

	public function getAllUsers(Request $request, $slug = null)
	{
		$model = $this->getModel();
		$params = static::getParamsFromRequest($request, ['profession' => $slug]);

		$entities = User::getAllUsers($model::$type, $params);
		$data = $this->_presetData([
			'relation' => $model::$tableName,
			'entity_type' => $model::$type,
			'entities' => $entities,
		]);
		switch(request()->route()->getName()) {
			case 'professional.list.frontend':
				$data['professions_users_count'] = Profession::getProfessionsListWithUsersCount();
				if($slug) {
					$profession = Profession::getEntity(['slug' => $slug], '');
					$data['profession_name'] = ' - ' . $profession['name'];
				}
				break;
			default:
				break;
		}
		$data['ad_user'] = AdUser::getAdUserForView($model::$type, (isset($profession) && !is_null($profession) ? $profession['profession_id'] : 0), $params);
		if (!empty($entities)) {
            $data['ads'] = Ads::getByParam('all', $params, ['search_type' => $model::$type], 1);
        }

		return strpos($request->route()->getName(), 'list.frontend.api') !== false  ? response()->json($data) : $this->showData($data);
	}

	public function getUserBySlug(Request $request, $slug)
	{
		$model = $this->getModel();
		$user = User::getUserBySlug($model::$type, $slug, $request);

		if(!is_array($user)) return redirect(route('home'));

		$data = $this->_presetData([
			'user' => $user,
		]);

        if ($model::$type == 'all') {
            if (strpos($user['role'], '_') !== false) {
                $tmpRole = implode('', array_map('ucfirst', explode('_', $user['role'])));
            } else {
                $tmpRole = ucfirst($user['role']);
            }
            //dd($model::$type, $user['role'], $tmpRole);
            if ($tmpRole == 'All') {
                return redirect(route('home'));
            }
            $model = new $tmpRole;
            $data['route_name'] = $model::$type.'.view.frontend';
        }
        
        if ($data['user'] && isset($data['user']['email']) && in_array($data['user']['email'], User::$userEmailsToChange)) {
            $data['user']['email'] = 'info@medicaleer.com';
        }
		$openingFieldsRoles = array('professional', 'architect', 'architect_firm', 'agent', 'agency');
		if( in_array($model::$type, $openingFieldsRoles) ) {
			$openingFields = $model::getOpeningFields();
			foreach($openingFields as $k => $v) {
				if(!empty($data['user'][$k])) {
					$data['opening_fields'] = $openingFields;
					break;
				}
			}
		}
		/*switch ($model::$type) {
			case 'artist':
				foreach(Country::all() as $i => $country) {
                	$data['countries'][$country->id] = $country->name;
            	}
            	$data['art_categories'] = ArtCategory::getAllListParent();
            	$data['art_categories_front'] = ArtCategory::getAllList();
				break;
			case 'seller':
				$data['product_categories_front'] = ProductCategory::getAllList();
				break;
			
			default:
				break;
		}*/
		/*if ($model::$type == 'artist') {
            foreach(Country::all() as $i => $country) {
                $data['countries'][$country->id] = $country->name;
            }
        }*/
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
        }
        if(isset($data['user']['arts']) || isset($data['user']['gallery'])) {
        	$data['art_categories'] = ArtCategory::getAllListParent();
            $data['art_categories_front'] = ArtCategory::getAllList();
        }
        if(isset($data['user']['products'])) {
        	$data['product_categories_front'] = ProductCategory::getAllList();
        }
        if(isset($data['user']['wines'])) {
        	$data['wine_categories_front'] = WineCategory::getAllList();
        }
        if(isset($data['user']['furnitures'])) {
        	$data['furniture_categories_front'] = FurnitureCategory::getAllList();
        }
        if(isset($data['user']['goods'])) {
        	$data['good_categories'] = GoodCategory::getAllListParent();
        	$data['good_categories_front'] = GoodCategory::getAllList();
        }
        if(isset($data['user']['designs'])) {
        	$data['design_categories'] = DesignCategory::getAllListParent();
        	$data['design_categories_front'] = DesignCategory::getAllList();
        }
		return $this->showData($data);
	}

	public function setUserStatus(Request $request, $id, $status)
	{
		$model = $this->getModel();
		if($id > 0) {
			$user = User::setUserStatus($id, $status);
		}
		if($user !== false) {
			$data = $user->toArray();
			$status = $data['status'];
			if(!empty($status)) {
				$attributes = array();
				foreach($user->toArray() as $key => $value) {
					if(!is_null($value) && !is_array($value)) {
						$attributes['user_' . $key] = $value;
					}
				}
				Email::send($data['status'] == 1 ? 'approve_user' : 'reject_user', $attributes);
			}
		}
		
		if ($status == 4 && $user !== false) {
            Property::query()->where('author', $data['id'])->update(['status' => 6]);
        } elseif ($status == 1 && $user !== false) {
            Property::query()->where('author', $data['id'])->update(['status' => 1]);
        }

		return redirect()->back();
	}

	public function setUserLabel(Request $request, $id, $label)
	{
		$model = $this->getModel();
		$model::setUserLabel($id, $label);

		return redirect()->back();
	}

	public function setAgentStatus(Request $request, $id, $status)
	{
		AgencyAgents::setAgentStatus($id, $status);

		return redirect()->back();
	}

	public function saveUserSearch(Request $request) {
		$data = SavedSearches::saveUserSearch($request);

		return Response::json([
			'saved' => $data,
			'message' => 'Search results saved successfully'
		], 200);
	}

	public function deleteUserSearch(Request $request, $id) {
		SavedSearches::where('id', $id)->delete();
		return redirect(route('user.profile.saved_searches'));
	}

	public function updateAllSearches(Request $request) {
		return SavedSearches::updateAllSearches();
	}

	public function saveUserProjects(Request $request) {
		$data = Projects::saveUserProjects($request);

		return Response::json([
			'saved' => $data,
			'message' => 'Done'
		], 200);
	}

	public function dbImporterView(Request $request) {
		$data = $this->_presetData();
		return $this->showData($data);
	}

	public function dbImporterImport(Request $request) {
		$response = DbImporter::getDataFromOldDb($request->all());
		return Response::json($response, 200);
	}

	public function addWatermarksToImages(Request $request) {
		$response = DbImporter::addWatermarksToImages($request->all());
		return Response::json($response, 200);
	}

	public function bulkEditUsers(Request $request) {
		$usersList = $request->get('editItems');
		$statusId = $request->get('status');
		$usersList = explode(',',$usersList);
		$model = $this->getModel();
		if (!empty($usersList) && isset($statusId)) {
			foreach($usersList as $user) {
				static::setUserStatus($request, $user, $statusId);
			}
			return Response::json([
				'users' => [],
				'message' => 'Done'
			], 200);
		} else {
			return Response::json([
				'users' => [],
				'message' => 'Error'
			], 200);
		}
	}

	public function bulkLabelUsers(Request $request) {
		$usersList = $request->get('editItems');
		$statusId = $request->get('status');
		$usersList = explode(',',$usersList);
		$label = $request->get('label');
    $label = !empty($label) ? $label : '';
    if ( !empty($label) && ($label === 'remove') ) {
      $label = 0;
    }
		$model = $this->getModel();
		if (!empty($usersList) && isset($label)) {
			foreach($usersList as $user) {
					$model::setUserLabel($user, $label);
			}
			return Response::json([
				'users' => [],
				'message' => 'Done'
			], 200);
		} else {
			return Response::json([
				'users' => [],
				'message' => 'Error'
			], 200);
		}
	}

	public function bulkDeleteUsers(Request $request) {
		$usersList = $request->get('editItems');
		$usersList = explode(',',$usersList);
		$model = $this->getModel();
		if (!empty($usersList)) {
			foreach($usersList as $user) {
				$model::deleteUser($user);
			}
			return Response::json([
				'users' => [],
				'message' => 'Done'
			], 200);
		} else {
			return Response::json([
				'users' => [],
				'message' => 'Error'
			], 200);
		}
	}

	public function searchUsers(Request $request) {
		$role = $request->get('role');
		$keyword = $request->get('keyword');

		if(isset($keyword)) {
			$data = User::searchUsers($role, $keyword, $request->get('user'));
			return Response::json([
				'results' => $data,
				'message' => 'Done'
			], 200);
		} else {
			return Response::json([
				'results' => [],
				'message' => 'Error'
			], 200);
		}
	}

	public function applyConsents(Request $request) {
		Cookie::queue('apply-consents', true, 2628000);
		return Response::json([
			'message' => 'Done'
		], 200);
	}

    public function getAllUsersJson(Request $request, $slug = null)
    {
        $model = $this->getModel();
        $params = static::getParamsFromRequest($request, ['profession' => $slug]);
        $entities = User::getAllUsers($model::$type, $params);
        $data = $this->_presetData([
            'relation' => $model::$tableName,
            'entity_type' => $model::$type,
            'entities' => $entities,
        ]);
        switch(request()->route()->getName()) {
            case 'professional.list.frontend':
                $data['professions_users_count'] = Profession::getProfessionsListWithUsersCount();
                if($slug) {
                    $profession = Profession::getEntity(['slug' => $slug], '');
                    $data['profession_name'] = ' - ' . $profession['name'];
                }
                break;
            default:
                break;
        }
        
        return Response::json($data, 200);
    }
    
    public function getUserBySlugJson(Request $request, $slug)
    {
        $model = $this->getModel();
        $user = User::getUserBySlug($model::$type, $slug);
        if(!is_array($user)) {
            return Response::json([
                'user' => null,
                'type' => $model::$type
            ], 404);
        }
        
        $data = $this->_presetData([
            'user' => $user,
        ]);
        if ($model::$type == 'all') {
            if (strpos($user['role'], '_') !== false) {
                $tmpRole = implode('', array_map('ucfirst', explode('_', $user['role'])));
            } else {
                $tmpRole = ucfirst($user['role']);
            }
            $model = new $tmpRole;
            $data['route_name'] = $model::$type.'.view.frontend';
        }
        
        if ($data['user'] && isset($data['user']['email']) && in_array($data['user']['email'], User::$userEmailsToChange)) {
            $data['user']['email'] = 'info@medicaleer.com';
        }
        
        $openingFieldsRoles = array('professional', 'architect', 'architect_firm', 'agent', 'agency');
        if( in_array($model::$type, $openingFieldsRoles) ) {
            $openingFields = $model::getOpeningFields();
            foreach($openingFields as $k => $v) {
                if(!empty($data['user'][$k])) {
                    $data['opening_fields'] = $openingFields;
                    break;
                }
            }
        }
        
        return Response::json($data, 200);
    }
}

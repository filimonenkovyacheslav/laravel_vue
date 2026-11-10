<?php

namespace App\Http\Controllers;

use App\Http\Models\Tags\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Models\Tags\Feature;
use Auth;
use BaseModel;
use User;
use CurrencyConverter;
use Property;
use JobCategory;
use ElasticSearchHelper;
use Response;
use Profession;
use Measure;
use CustomLaravelLocalization;
use Partner;
use Page;
use Seller;
use Wineseller;
use Furnitureseller;
use Brand;
use Product;
use Wine;
use News;
use Furniture;
use Good;
use Design;
use Art;
use ArtCategory;
use DesignCategory;
use PropertyCategory;
use WineCategory;
use FurnitureCategory;
use MenuCategoryItem;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public $model;

	public function index(Request $request)
	{
		$data = $this->_presetData([
			'counter' => BaseModel::geCounterData(),
		]);
		return $this->showData($data);
	}

	public function setLang(Request $request, $code)
	{
		if(isset($code) && !empty($code) && CustomLaravelLocalization::isSupportedLocale($code)) {
			CustomLaravelLocalization::setLocaleLL($code);
			return redirect()->back()->withCookie(cookie()->forever('lang', $code));
		}
		return redirect(route('home'));
	}

	public function getModel($suffix = '') {
		if(empty($this->model)) {
			$model = explode("\\", get_class($this));
			$model = array_pop($model);
			$model = str_replace('Controller', '', $model);
			$this->model = $model . $suffix;

		}
		return $this->model;
	}

	public function getParamsFromRequest($request, $params = []) {
		$req = $request->except('_token');
		if (!empty($req['fcategory']) && empty($req['category'])) {
			$req['category'] = $req['fcategory'];
		}
		return array_filter(array_merge($req, $params));
	}

	public function showData($data) {
		$route_name = $data['route_name'];
		$agency_agents = $data['agency_agents'];
		$params = json_encode(collect($data));
		$seo_tags = $this->getSEOTags($data);
//dd($data);
		return view('index', compact('route_name', 'params', 'seo_tags', 'agency_agents'));
	}

	public function getSEOTags($data) {
		$siteName = config('app.name');
		$title = $siteName;
		if($data['route_name'] == 'home') {
			$title = $siteName;//. ' '.BaseModel::getSiteCountryName();
		} else {
			if(isset($data['entity']) && isset($data['entity']['title'])) {
				$title = $data['entity']['title'] . ' – '. $siteName;
				if(isset($data['entity']['uploadsList']) && sizeof($data['entity']['uploadsList']) > 0) {
				    $image = $data['entity']['uploadsList'][0]['name'];
					$image = strpos($image, '/uploads/') === false ? url('/uploads/'.$data['entity']['uploadsList'][0]['name']) : $image;
				}
				if(isset($data['entity']['description'])) {
					$desc = strip_tags($data['entity']['description']);
					$len = strlen($desc);
					if($len > 0) {
						$description = $len > 500 ? substr($desc, 0, 500).'...' : $desc;
					}
				}
			} else if(isset($data['user']) && is_array($data['user'])) {
				$user = $data['user'];
				if(isset($user['type']) && User::_isAgency($user['type'])) {
					$title = isset($user['relation']) && isset($user['relation']['company_name']) ? $user['relation']['company_name'] : $user['first_name'] . ' ' . $user['last_name'];
				} else {
					$title = isset($user['first_name']) ? $user['first_name'] . ' ' . $user['last_name'] : 'Profile';
				}
				$title .= ' – '. $siteName;
			} else if(isset($data['entity_type'])) {
				if (isset($data['entity_types'][$data['entity_type']])) {
					$title = __('All') . ' ' . $data['entity_types'][$data['entity_type']] . ' – '. $siteName;
				}
			} else if(strpos(request()->url(), '/admin/')) {
				$title = 'Profile – '. $siteName;
			} else {
				$segments = request()->segments();
				$title = implode(' ', array_map('ucfirst', explode('-', end($segments)))) . ' – '. $siteName;
			}
		}
		$tags = ['title' => $title];
		if(isset($description)) {
			$tags['description'] = $description;
		}
		if(isset($image)) {
			$tags['image'] = $image;
		}
		return $tags;
	}

	public function getElasticSearchResults(Request $request) {
		$useElasticSearch = config('app')['use_elastic_search'];

		if($useElasticSearch) {
			$params = static::getParamsFromRequest($request);
			$params['search_type'] = str_plural($params['search_type']);
			$results = ElasticSearchHelper::searchElastic($params['keyword'], $params['search_type']);
			return Response::json(['message' => 'Done', 'results' => $results], 200);
		} else {
			return Response::json(['message' => 'Done'], 200);
		}
	}

	protected function _getAvailableEntitiesSlugs($entities = []){
	    return array_merge($entities,[
            //'property',
            //'agency',
            //'design',
            //'art',
            'product',
            'good',
            'brand',
            'news',
            //'furniture',
            //'wine',
            'professional'
        ]);
    }
    
    protected function _getAvailableRoles($entities = []){
        $user = Auth::user();
        $forAdminRoles = $user && $user->isAdmin() ? ['architect_firm', 'building_company', 'design_company'] : [];
        return array_merge($entities,[
            //'agency',
            //'artist',
            //'agent',
            //'gallery',
            'professional',
            'seller',
            //'wineseller',
            //'furnitureseller',
            'brand',
            /*'architect_firm',
            'building_company'*/
        ], $forAdminRoles);
    }
    protected function isFrontRoute($route) {
    	return (strpos('home', $route) !== 0 && strpos('admin.', $route) !== 0);
    }

	protected function _presetData($attrs = []) {
		$request = request();
		$user = $request->user();
		$entities = [];
		$menu_categories = MenuCategoryItem::all();
		foreach ($menu_categories as $item) {
			$entities[$item->slug] = __($item->label);
		}

		$entities = array_merge($entities, User::getRelativeData());

        $entities = array_filter($entities, function($item,$key){
            if (in_array($key,$this->_getAvailableEntitiesSlugs())) {
                return $item;
            }
        },ARRAY_FILTER_USE_BOTH);
        $route = $request->route()->getName();
		$data = [
			'site_name' => config('app.name'),
			'route_name' => $route,
			'user_role' => !empty($user) ? $user->role()->value('name') : '',
            'homeimage' => Page::getHomeImage(CustomLaravelLocalization::getDomainLocale()),
            'footerimage' => $this->isFrontRoute($route) ? Page::getFooterImage(CustomLaravelLocalization::getDomainLocale()) : [],
			'entity_types' => $entities,
			'currencies' => CurrencyConverter::getCurrenciesForSelect(),
			'features' => Feature::getEntities(),
			'property_types' => Property::getPropertyData('property_type'),
			'property_subtypes' => Property::getPropertyData('property_subtype'),
			'property_statuses' => Property::getPropertyData('property_status'),
			'property_rent_schedule' => Property::getPropertyData('property_rent_schedule'),
			'property_type_links' => Property::$propertyTypeLinks,
			'use_elastic_search' => config('app')['use_elastic_search'],
			'agency_agents' => User::getAgencyAgents(),
			'professions' => Profession::getAllList(),
            'professions_parent' => Profession::getAllListParent(),
            'product_categories' => ProductCategory::getAllListParent(),
            'wine_categories' => WineCategory::getAllListParent(),
            'furniture_categories' => FurnitureCategory::getAllListParent(),
            /*'design_categories' => DesignCategory::getAllListParent(),
            'property_categories' => PropertyCategory::getAllListParent(),
            'art_categories' => ArtCategory::getAllListParent(),*/
            //'product_categories_filter' => ProductCategory::getCategoriesHierarchy(0, '', false, true),
            //'product_categories_admin' => ProductCategory::getAllList(true),
            //'product_categories_front' => ProductCategory::getAllList(),
            //'art_categories_admin' => ArtCategory::getAllList(true),
            //'art_categories_front' => ArtCategory::getAllList(),
            //'design_categories_admin' => DesignCategory::getAllList(true),
            //'design_categories_front' => DesignCategory::getAllList(),
            'seller_types' => Seller::getTypes([0]),
            'wineseller_types' => Wineseller::getTypes([0]),
            'furnitureseller_types' => Furnitureseller::getTypes([0]),
            'product_labels' => Product::getProductData('label'),
            'wine_labels' => Wine::getWineData('label'),
            'news_labels' => News::getNewsData('label'),
            'furniture_labels' => Furniture::getFurnitureData('label'),
            'good_labels' => Good::getGoodData('label'),
            'art_labels' => Art::getArtData('label'),
            'design_labels' => Design::getDesignData('label'),
			'measure_default' => Measure::getMeasureByCode(CustomLaravelLocalization::getDefaultAreaMeasureCode(), true)
		];
		$data['professions_sort'] = array_keys($data['professions']);
		//dd($data, CustomLaravelLocalization::getDomainLocale());
		return array_merge($data, $attrs);
	}

	public function createJsVarsFile(Request $request) {
		BaseModel::createJsVarsFile($request);
	}

	public function createLangJsFile() {
		BaseModel::createLangJsFile();
	}

	public function clearCache() {
		\Artisan::call('cache:clear');
		return redirect()->back();
	}
}

<?php

namespace App\Http\Controllers\Properties;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Property;
use PropertyPrice;
use PropertyFavorite;
use UploadProperty;
use Feature;
use Response;
use Email;
use User;
use Auth;
use AgencyAgents;
use Country;
use CustomLaravelLocalization;
use Measure;
use Ads;
use PropertyCategory;
use AddressKeyword;

class PropertyController extends Controller
{
    public $model;
	public $imagesModel;
	public $tableKey = 'id';
	
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['getAllProperties', 'getPropertyBySlug', 'getAllPropertiesJson', 'getPropertyBySlugJson', 'searchLocations']]);
	}

	public function getAllProperties(Request $request)
	{
		$model = $this->getModel();
		$orderBy = $model::getSortOrder($request);
		//$category = request('category', 0);
		$params = static::getParamsFromRequest($request);
        $category = empty($params['category']) ? 0 : $params['category'];
        
		$data = $this->_presetData([
			'entity_type' => 'property',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order']),
            'selected_parents' => empty($category) ? [] : array_reverse(PropertyCategory::getSelectedCategoryParents($category)),
            'property_categories_filter' => PropertyCategory::getCategoriesHierarchy($category, '', false, true, false, true),
            //'property_categories' => PropertyCategory::getAllListParent(),
            'property_categories_front' => PropertyCategory::getAllList(),
        ]);
        if ($data['entities']->total()) {
            $data['ads'] = Ads::getByParam('all', static::getParamsFromRequest($request), ['search_type' => 'property'], 1);
        }
		//dd($data);
		return $this->showData($data);
	}

	public function getPropertyBySlug(Request $request, $param)
	{
		$model = $this->getModel();
		$property = $model::getByParam('slug', $param, ['user', 'country']);
		if(!isset($property['id']) || $property['status'] == 5) return redirect('404');

		$data = $this->_presetData([
            'entity' => $property,
			'entities_similar' => $model::getAll([
				'property_type' => $property['property_type'],
				'property_subtype' => $property['property_subtype'],
				'country' => $property['country'],
				'city' => $property['city'],
				'not_in' => [$property['id']],
			], ['user']),
			'property_categories_front' => PropertyCategory::getAllList(),
        ]);
		//dd($data);
		return $this->showData($data);
	}

	public function editProperty(Request $request, $param = null)
	{
        $user = Auth::user();
		$model = $this->getModel();
		$property = $model::_addTranslation($model::where('properties.id', $param))->first();
		if(!is_null($param)) {
			if(!isset($property['author']) || ($user->id != $property['author'] && !$user->isAdmin() && AgencyAgents::getAgencyId($property['author']) != $user->id)) {
				return redirect(url('/'));
			}
		}
		
        if(in_array($user->role()->first()->name,['artist', 'seller'])) {
            return redirect(url('/'));
        }
		$data = $this->_presetData([
            'id' => $param,
			'measures' => Measure::getMeasuresForSelect(),
		]);
		foreach(Country::all() as $i => $country) {
			$data['countries'][$country->id] = $country->name;
			$data['countries_codes'][$country->iso2] = $country->id;
		}
		return $this->showData($data);
	}

	public function _getProperty(Request $request, $param = null)
	{
		$model = $this->getModel();
		$entity = $model::getByParam('id', $param);
		//dd($entity);
		return Response::json(['entity' => $entity], 200);
	}

	public function deleteProperty(Request $request, $id)
	{
		$model = $this->getModel();
		$model::deletePropertyById($id);
		return redirect()->back();
	}

	public function unpublishProperty(Request $request, $id)
	{
		return $this->setPropertyStatus($request, $id, 6);
	}

	public function bulkEditProperties(Request $request) {
		$propertiesList = $request->get('editItems');
		$propertiesList = explode(',',$propertiesList);
		$statusId = $request->get('status');
		$model = $this->getModel();
		if ( !empty($propertiesList) && isset($statusId) ) {
			foreach($propertiesList as $property) {
				static::setPropertyStatus($request, $property, $statusId);
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

  public function bulkLabelProperties(Request $request) {
		$propertiesList = $request->get('editItems');
		$propertiesList = explode(',', $propertiesList);;
    $label = $request->get('label');
    $label = !empty($label) ? $label : '';
    if ( !empty($label) && ($label === 'remove') ) {
      $label = 0;
    }
		$model = $this->getModel();
		if ( !empty($propertiesList) && isset($label) ) {
			foreach($propertiesList as $property) {
    		$model::setPropertyLabel($property, $label);
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

	public function bulkDeleteProperties(Request $request) {
		$propertiesList = $request->get('editItems');
		$propertiesList = explode(',',$propertiesList);
		$model = $this->getModel();
		if ( !empty($propertiesList) ) {
			foreach($propertiesList as $property) {
				$model::deletePropertyById($property);
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

	public function saveProperty(Request $request)
	{
		$model = $this->getModel();
		$result = $model::saveItem($request);
		if($result && is_array($result) && empty($result['errors'])) {
			if(is_null($request->id) || empty($request->id)) {
				$attributes = [
					'entity_url' => url(route('property.view.frontend', ['slug' => $result['slug']])),
					'entity_title' => $result['title']
				];
				Email::send('new_property', $attributes);
				return Response::json(['message' => __('New Property was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('property.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
			}
			return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
		}
		return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
	}

	public function setPropertyStatus(Request $request, $id, $status)
	{
		$model = $this->getModel();
		$property = $model::setPropertyStatus($id, $status);

		if($property === false) {
			return redirect(route('user.profile.properties'));
		}

		if(isset($property['status']) && $property['status'] == 1) {
			$attributes = [
				'entity_url' => url(route('property.view.frontend', ['slug' => $property['slug']])),
				'entity_title' => $property['title'],
				'edit_url' => url(route('property.edit.admin', ['id' => $property['id']])),
			];
			$user = User::findOrFail($property['author']);
			foreach($user->toArray() as $key => $value) {
				if(!is_null($value) && !is_array($value)) {
					$attributes['user_' . $key] = $value;
				}
			}
			Email::send('approve_property', $attributes);
		}
		return redirect()->back();
	}

	public function setPropertyLabel(Request $request, $id, $label)
	{
		$model = $this->getModel();
		$model::setPropertyLabel($id, $label);

		return redirect()->back();
	}

	public function toggleFavoriteProperty(Request $request)
	{
		$favorite = PropertyFavorite::toggleFavoriteProperty($request->except('_token'));

		return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
	}

	public function updatePropertyPrices() {
		PropertyPrice::updatePrices();
	}

	public function getImagesModel() {
		if(empty($this->imagesModel)) {
			$this->imagesModel = UploadProperty::class;
		}
		return $this->imagesModel;
	}
    
    public function searchCountries(Request $request) {
        $keyword = $request->get('keyword');
        
        if(isset($keyword)) {
            $data = Country::searchCountries($keyword);
            return Response::json([
                'data' => $data,
                'message' => 'Done'
            ], 200);
        } else {
            return Response::json([
                'data' => [],
                'message' => 'Error'
            ], 200);
        }
    }
    public function uniqueMergeLocations($locationsOld, $locationsNew) {
    	if (!is_array($locationsOld) || empty($locationsOld)) {
    		return is_array($locationsNew) ? $locationsNew : array();
    	}
    	if (!is_array($locationsNew) || empty($locationsNew)) {
    		return is_array($locationsOld) ? $locationsOld : array();
    	}
    	$locations = $locationsOld;
    	foreach ($locationsNew as $data) {
    		$labelNew = $data['name'];
    		$uniq = true;
    		foreach ($locationsOld as $d) {
    			$labelOld = empty($d['keyword']) ? $d['name'] : $d['keyword'];
    			if ($labelOld == $labelNew) {
    				$uniq = false;
    				break;
    			}
    		}
    		if ($uniq) {
    			$locations[] = $data;
    		}
    	}
    	return $locations;
    }
    
    public function searchLocations(Request $request) {
        $keyword = $request->get('keyword');
        
        if(isset($keyword)) {
        	$maxResults = 15;
        	$locations = AddressKeyword::searchKeywords('property', $keyword);
        	$rest = $maxResults - sizeof($locations);
        	if ($rest > 0) {
	            $locations = $this->uniqueMergeLocations($locations, Property::searchPropertyForLocations($keyword, ($rest > 10 ? 10 : $rest)));
	            //dd($rest, $locations);
	            $rest = $maxResults - sizeof($locations);
	            if ($rest > 0) {
    	        	//$countries = Country::searchCountriesAsLocations($keyword, 5);
            		$locations = $this->uniqueMergeLocations($locations, Country::searchCountriesAsLocations($keyword, $rest));
            	}
            }
            return Response::json([
                'data' => $locations,
                'message' => 'Done'
            ], 200);
        } else {
            return Response::json([
                'data' => [],
                'message' => 'Error'
            ], 200);
        }
    }
    
    public function getAllPropertiesJson(Request $request) {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'property',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
        
        return Response::json($data, 200);
    }
    
    public function getPropertyBySlugJson(Request $request, $param) {
        $model = $this->getModel();
        $property = $model::getByParam('slug', $param, ['user', 'country']);
        if(!isset($property['id']) || $property['status'] == 5) {
            return Response::json([
                'entity' => null
            ], 404);
        }
    
        $data = $this->_presetData([
            'entity' => $property,
            'entities_similar' => $model::getAll([
                'property_type' => $property['property_type'],
                'property_subtype' => $property['property_subtype'],
                'country' => $property['country'],
                'city' => $property['city'],
                'not_in' => [$property['id']],
            ], ['user']),
        ]);
        
        return Response::json($data, 200);
    }
}

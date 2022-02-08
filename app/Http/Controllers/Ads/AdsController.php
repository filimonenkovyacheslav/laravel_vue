<?php

namespace App\Http\Controllers\Ads;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Upload;
use Feature;
use Response;
use User;
use App\Http\Models\Ads\Ads;
use UploadsAds;

class AdsController extends Controller
{
    public $model;
    public $imagesModel;
    public $tableKey = 'ads_id';
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function editAds(Request $request, $param = null)
    {
        $user = Auth::user();
        $admin = $user->isAdmin();
        if (!$admin) {
            return redirect(url('/'));
        };
        
        $data = $this->_presetData([
            'ads_id' => $param,
        ]);
        return $this->showData($data);
    }
    
    public function _getAds(Request $request, $param = null)
    {
        $model = $this->getModel();
        $entity = $model::getByParam('ads_id', $param);
        return Response::json(['entity' => $entity], 200);
    }
    
    public function deleteAds(Request $request, $id)
    {
        Ads::deleteAdsById($id);
        return redirect()->back();
    }
    
    public function saveAds(Request $request)
    {
        $result = Ads::saveItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            return Response::json(['message' => __('Done'), 'ads_id' => $result['ads_id'], 'redirect' => route('ads.edit.admin', ['id' => $result['ads_id']]), 'entity' => Ads::getByParam('ads_id', $result['ads_id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }
    
    public function unpublishAds(Request $request, $id)
    {
        return $this->setAdsStatus($request, $id, 6);
    }
    
    public function setAdsStatus(Request $request, $id, $status)
    {
        $ad = Ads::setStatus($id, $status);
        return redirect()->back();
    }
    
    public function bulkDeleteAds(Request $request) {
        $adsList = $request->get('editItems');
        $adsList = explode(',',$adsList);
        $model = $this->getModel();
        if (!empty($adsList)) {
            foreach($adsList as $ads) {
                $model::deleteAdsById($ads);
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
    
    public function bulkApproveAds(Request $request) {
        $adsList = $request->get('editItems');
        $adsList = explode(',',$adsList);
        $model = $this->getModel();
        if (!empty($adsList)) {
            foreach($adsList as $ads) {
                $model::setStatus($ads, 1);
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
    
    public function bulkUnpublishAds(Request $request) {
        $adsList = $request->get('editItems');
        $adsList = explode(',',$adsList);
        $model = $this->getModel();
        if (!empty($adsList)) {
            foreach($adsList as $ads) {
                $model::setStatus($ads, 6);
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

    public function _getAdsCategoryFields(Request $request)
    {
        $model = $this->getModel();        
        return Response::json(['categories' => $model::_getAdsFieldsList()], 200);
    }

    public function getAdsTypes($id)
    {
        $model = $this->getModel();
        return Response::json(['types' => $model::_getAdsTypes($id)], 200);
    }
}

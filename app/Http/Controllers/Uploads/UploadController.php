<?php

namespace App\Http\Controllers\Uploads;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use Upload;
use UploadProperty;

class UploadController extends Controller
{
	public $model;
	public $noWatermarkPostTypes = ['art', 'product', 'wine', 'furniture', 'good', 'design', 'property', 'user', 'news'];
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getAllPropertyUploads', 'getPropertyUploadById']]);
    }

    public function getAllPropertyUploads(Request $request)
    {
        $orderBy = UploadProperty::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'impression',
            'entities' => UploadProperty::getAll(static::getParamsFromRequest($request), $orderBy['order_by'], $orderBy['order']),
        ]);
        //dd($data);
        return $this->showData($data);
    }

    public function getPropertyUploadById(Request $request, $id)
    {
        $data = $this->_presetData([
            'entity' => UploadProperty::getById($id),
        ]);
        //dd($data);
        return $this->showData($data);
    }

    /**
     * Saving images uploaded through XHR Request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type = null)
    {
        ini_set('max_execution_time', 300);
        ini_set('max_input_time', 300);
		$model = $this->getModel();
		if ($type && in_array($type, $this->noWatermarkPostTypes)) {
            $data = $model::saveUploads($request, 'file', false);
        } else {
            $data = $model::saveUploads($request, 'file');
        }

		return Response::json([
			'data' => $data,
			'message' => __('Upload saved successfully')
		], 200);
    }

    /**
     * Remove the images from the storage.
     *
     * @param Request $request
     */
    public function destroy(Request $request)
    {
		$model = $this->getModel();
        $result = $model::deleteUpload($request);

		return $result
			? Response::json(['message' => __('File successfully delete')], 200)
			: Response::json(['message' => __('Sorry file does not exist')], 400);
    }
}

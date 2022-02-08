<?php

namespace App\Http\Models\Uploads;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\Response;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\Facades\Image;
use CustomLaravelLocalization;
use UploadProperty;
use UploadFranchise;
use UploadJobEntity;
use UploadProject;
use UploadUser;
use UploadsAds;
use UploadArt;
use UploadProduct;
use UploadWine;
use UploadFurniture;
use UploadDesign;
use UploadNews;
use DB;

class Upload extends Model
{
	private static $uploadPath;

	private static $watermarkName = 'watermark.png';
	private static $watermarkPath;
	public $fillable = ['type',	'name',	'is_featured', 'created_at', 'updated_at', 'watermark', 'caption'];
	public static $notAllowedExts = ['php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phtml', 'pl', 'asp', 'aspx', 'cgi', 'dll', 'exe', 'shtm', 'shtml', 'fcg', 'fcgi', 'fpl', 'asmx', 'pht', 'py', 'psp', 'rb', 'var', 'phar'];

	private static $uploadTypes = [
		'image' => 1,
		'video' => 2,
		//'attachment' => 3,
	];
	private static $relativeIds = [
		'job_entities' => 'job_entity_id',
		'properties' => 'property_id',
		'franchises' => 'franchise_id',
		'users' => 'user_id',
        'ads' => 'ads_id',
		'projects' => 'project_id',
        'arts' => 'art_id',
        'products' => 'product_id',
        'wines' => 'wine_id',
        'news' => 'news_id',
        'furnitures' => 'furniture_id',
        'goods' => 'good_id',
        'designs' => 'design_id'
	];
	private static $relativeModels = [
		'job_entities' => 'UploadJobEntity',
		'properties' => 'UploadProperty',
		'franchises' => 'UploadFranchise',
		'users' => 'UploadUser',
        'ads' => 'UploadsAds',
		'projects' => 'UploadProject',
        'arts' => 'UploadArt',
        'products' => 'UploadProduct',
        'wines' => 'UploadWine',
        'news' => 'UploadNews',
        'furnitures' => 'UploadFurniture',
        'goods' => 'UploadGood',
        'designs' => 'UploadDesign'
	];

	public static function getUploadsPath()
	{
		if(empty(static::$uploadPath)) {
			static::$uploadPath = public_path('/uploads');
		}
		return static::$uploadPath;
	}

	public static function getUploadsDir(){
		if(!is_dir(static::getUploadsPath())) {
            $oldmask = umask(0);
			mkdir(static::getUploadsPath(), 0777);
            umask($oldmask);
		}

		$maxId = static::max('id');
		$dir = ceil((($maxId ? $maxId : 0) + 1)/ 10000);
		$dirPath = static::getUploadsPath().'/'.$dir;
		if(!is_dir($dirPath)) {
            $oldmask = umask(0);
			mkdir($dirPath, 0777);
            umask($oldmask);
		}
		//chmod($dirPath, 0777);
		return $dir;
	}

	public static function getWatermarkPath()
	{
		if(empty(static::$watermarkPath)) {
			static::$watermarkPath = public_path('/images') . '/' . static::$watermarkName;
		}
		return static::$watermarkPath;
	}

	public static function setImageCaptions($entityId, $captions) {
		foreach($captions as $id => $caption) {
			static::where('id', $id)->update(['caption' => $caption]);
		}
	}

	public static function makeImageFeatured($entityId, $relativeTable, $featuredId) {
		$uploadType = 1;
		$uploads = static::getUploadedImages($entityId, $relativeTable, true, 1);
		if(sizeof($uploads) == 0) return;

		if(!isset($featuredId) || is_null($featuredId) || empty($featuredId)) {
			$featuredId = $uploads[0];
		}

		static::whereIn('id', $uploads)->update(['is_featured' => 0]);
		static::where('id', $featuredId)->update(['is_featured' => 1]);
	}

	public static function getUploadedMain($entityId)
	{
		$title_uploads_id = DB::table('news')->where('id', $entityId)->first()->title_uploads_id;
		$uploads = [];
		/*$uploads = DB::table('uploads')
		->join('uploads_' . $relativeTable, 'uploads_' . $relativeTable . '.upload_id', '=', 'uploads.id')
		->join($relativeTable, 'uploads_' . $relativeTable . '.' . static::$relativeIds[$relativeTable], '=', $relativeTable . '.' . $idType)
		->where([
			[$relativeTable . '.' . $idType, '=', $entityId],
			['uploads.id', '=', $title_uploads_id]
		]);

		if(isset($uploadType) && !is_null($uploadType)) {
			$uploads->where('uploads.type', $uploadType);
		}
		$uploads->groupBy('uploads.id')->orderBy('uploads.id');

		$uploads = $idsOnly ? $uploads->pluck('uploads.id') : $uploads->get(['uploads.*']);*/
		if ($title_uploads_id) {
			$uploads = DB::table('uploads')->where('id', '=', $title_uploads_id)->get();
		}		
		
		$uploads = !empty($uploads) ? $uploads->toArray() : $uploads;
		$uploads = json_decode(json_encode($uploads), true);

		/*foreach($uploads as $k => $v) {
			if(!empty($v['is_featured']) && $v['is_featured'] == 1) {
				$out = array_splice($uploads, $k, 1);
				array_splice($uploads, 0, 0, $out);
			}
		}*/

		return $uploads;
	}

	public static function getUploadedImages($entityId, $relativeTable, $idsOnly = false, $uploadType = null)
	{
        if ($relativeTable === 'ads') {
            $idType = 'ads_id';
        } else {
            $idType = 'id';
        }
        if ($relativeTable === 'news') {
        	$title_uploads_id = DB::table('news')->where('id', $entityId)->first()->title_uploads_id;
        	$uploads = DB::table('uploads')
			->join('uploads_' . $relativeTable, 'uploads_' . $relativeTable . '.upload_id', '=', 'uploads.id')
			->join($relativeTable, 'uploads_' . $relativeTable . '.' . static::$relativeIds[$relativeTable], '=', $relativeTable . '.' . $idType)
			->where([
				[$relativeTable . '.' . $idType, '=', $entityId],
				['uploads.id', '<>', $title_uploads_id]
			]);
        }
        else{
        	$uploads = DB::table('uploads')
			->join('uploads_' . $relativeTable, 'uploads_' . $relativeTable . '.upload_id', '=', 'uploads.id')
			->join($relativeTable, 'uploads_' . $relativeTable . '.' . static::$relativeIds[$relativeTable], '=', $relativeTable . '.' . $idType)
			->where([
				[$relativeTable . '.' . $idType, '=', $entityId],
			]);
        }
		
		if(isset($uploadType) && !is_null($uploadType)) {
			$uploads->where('uploads.type', $uploadType);
		}
		$uploads->groupBy('uploads.id')->orderBy('uploads.id');

		$uploads = $idsOnly ? $uploads->pluck('uploads.id') : $uploads->get(['uploads.*']);
		$uploads = !empty($uploads) ? $uploads->toArray() : $uploads;
		$uploads = json_decode(json_encode($uploads), true);

		foreach($uploads as $k => $v) {
			if(!empty($v['is_featured']) && $v['is_featured'] == 1) {
				$out = array_splice($uploads, $k, 1);
				array_splice($uploads, 0, 0, $out);
			}
		}
		return $uploads;
	}

	public static function getUploadTypeByUrl($file_link)
	{
		$file_type = explode('.',strrev($file_link))[0];
		return $file_link;
	}

	public static function getUploadedItemNews($entityId)
	{
		$upload_id = null;
		$upload = DB::table('uploads_news')->where('news_id', $entityId)->first();
		if ($upload) {
			$upload_id = $upload->upload_id;
			$upload = DB::table('uploads')->where('id', $upload_id)->first()->name;
		}

		return $upload;
	}

	public static function saveUploadedImages($uploads, $entityId, $relativeTable)
	{
		$oldUploads = static::getUploadedImages($entityId, $relativeTable, true);
		$model = static::$relativeModels[$relativeTable];

		if(!empty($oldUploads)) {
			$model::whereIn('upload_id', $oldUploads)->delete();
		}
		$model::where(static::$relativeIds[$relativeTable], $entityId)->delete();
		if(!empty($uploads)) {
            !is_array($uploads) && $uploads = [$uploads];
			foreach ($uploads as $p) {
				$item = new $model;
				$item->fill([static::$relativeIds[$relativeTable] => $entityId, 'upload_id' => $p]);
				$item->save();
			}
		}
	}

	public static function saveUploadedVideos($uploads, $entityId, $relativeTable = 'properties')
	{
		$model = static::$relativeModels[$relativeTable];
        $primeKey = static::$relativeIds[$relativeTable];

		$model::join('uploads', 'uploads.id', '=', 'upload_id')
			->where($primeKey, '=', $entityId)
			->where('uploads.type', '=', 2)
			->delete();
		if(!empty($uploads) && is_array($uploads)) {
			foreach ($uploads as $id) {
				$item = new $model;
				$item->fill([static::$relativeIds[$relativeTable] => $entityId, 'upload_id' => $id]);
				$item->save();
			}
		}
	}
    
    public static function deleteUploadedEntityImages($entityId, $relativeTable)
    {
        $oldUploads = static::getUploadedImages($entityId, $relativeTable, true);
        $model = static::$relativeModels[$relativeTable];
        
        if(!empty($oldUploads)) {
            $model::whereIn('upload_id', $oldUploads)->delete();
            foreach ($oldUploads as $oldUpload) {
                $deleted = static::deleteUpload([], $oldUpload);
            }
        }
        $model::where(static::$relativeIds[$relativeTable], $entityId)->delete();
        
        return true;
    }

	public static function attachUploads($data, $request, $fields, $watermark = true) {
		foreach($fields as $k => $v) {
			$saved = static::saveUploads($request, $v, $watermark);

			if(!empty($saved[0])) {
				$key = str_replace('_id', '', $v);
				$data[$key] = $saved[0];
			}
		}
		return $data;
	}

	public static function getUploadById($id) {
		$upload = static::where('id', $id)->first();
		return !empty($upload) ? $upload->toArray() : [];
	}

	/*public static function saveRemoteUpload($path) {
		$id = null;
		if(static::isUrlExists($path)) {
			$dir = static::getUploadsDir();
			$pathInfo = pathinfo($path);
			$basename = $dir.'/'.$pathInfo['filename'];
			$extension = '.'.$pathInfo['extension'];
			$name = static::getCorrectFileName($basename, $extension);

			file_put_contents(public_path('uploads/'.$name), file_get_contents($path));
			$upload = new Upload();
			$upload->type = 1;
			$upload->name = $name;
			$upload->save();
			static::addWatermark($name, $upload->id);
			$id = $upload->id;
		}
		return $id;
	}

	public static function isUrlExists($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $code == 200;
	}*/

	public static function saveUploads($request, $field, $watermark = true) {
		$uploads = $request->file($field);
		$data = [];

		if(!empty($uploads)) {
			$dir = static::getUploadsDir().'/';

			if (!is_array($uploads)) {
				$uploads = [$uploads];
			}
			for ($i = 0; $i < count($uploads); $i++) {
				$upload = $uploads[$i];
				$type = 0;
				$mimeType = $upload->getClientMimeType();
				$extension = $upload->getClientOriginalExtension();

				//$notAllowedExts = array("jpg", "jpeg");
				//$extension = end(explode(".", $_FILES["file"]["name"]));
				if (in_array(strtolower($extension), static::$notAllowedExts)) {
   					continue;
				}

				$basename = $dir.str_replace('.' . $extension, '', basename($upload->getClientOriginalName()));
				//$resizeIndex = $basename . '-250x250';
				$name = static::getCorrectFileName($basename, $extension);
				$upload->move(static::getUploadsPath().'/'.$dir, str_replace($dir, '', $name));

				foreach(static::$uploadTypes as $k => $v) {
					if(strpos($mimeType, $k) === 0) {
						$type = $v;
					}
				}
				$uploadItem = new Upload();
				$uploadItem->type = $type;
				$uploadItem->name = $name;
				if(!$watermark) {
					$uploadItem->watermark = 0;
				}
				$uploadItem->save();
				$data[] = $uploadItem->id;

				// temporary remove watermark in uploads files
				// if($type === 1 && $watermark) {	// image
					//Image::make($upload)->resize(250, null, function ($constraints) {
					//	$constraints->aspectRatio();
					//})->save(static::getUploadsPath() . '/' . $resizeName);

					// Add watermark
					// static::addWatermark($name, $uploadItem->id);
				// }
			}
		}
		return $data;
	}

	public static function deleteUpload($request, $id = 0) {
		$upload = Upload::where('id', empty($id) ? $request->id : $id)->first();

		if(empty($upload)) {
			return false;
		}
		$filePath = static::getUploadsPath() . '/' . $upload->name;

		if(file_exists($filePath)) {
			unlink($filePath);
		}
		//if(file_exists($resizedFile)) {
		//	unlink($resizedFile);
		//}
		if(!empty($upload)) {
			$upload->delete();
		}
		return true;
	}
	public static function cloneUpload($id) {
		$item = Upload::where('id', $id)->first();

		if(empty($item)) {
			return false;
		}
		$old = $item->toArray();
		$clone = new Upload();
		unset($old['id']);
		//dd($old);
		$clone->fill($old)->save();
		
		return $clone->id;
	}

	public static function getCorrectFileName($basename, $extension, $source = 'folder') {
		$index = '';
		$iter  = 0;
		$extension = strpos($extension, '.') === 0 ? $extension : '.'.$extension;
        $basename = preg_replace('/\s+/','',$basename);
        $basename = preg_replace('/[^a-zA-Z0-9\.\/]/s', '-', $basename);

		do {
			$name = $basename . $index . $extension;
			$iter++;
			$index = '(' . $iter . ')';
		} while($source == 'db' ? static::where('name', $name)->exists() : file_exists(static::getUploadsPath() . '/' . $name));
		return $name;
	}

	public static function addWatermark($name, $id) {
		$path = static::getUploadsPath() . '/' . $name;

		if(static::where('id', $id)->value('watermark') === null) {
			$image = Image::make($path);
			$watermark = Image::make(static::getWatermarkPath())->resize($image->getSize()->width/2, null, function($constraints) {
				$constraints->aspectRatio();
			});
			$image->insert($watermark, 'center');
			$image->save(static::getUploadsPath() . '/' . $name);
			$image->destroy();
			static::where('id', $id)->update(['watermark' => 1]);
		}
	}
}

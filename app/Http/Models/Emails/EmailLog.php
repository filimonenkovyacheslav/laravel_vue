<?php

namespace App\Http\Models\Emails;

use Illuminate\Database\Eloquent\Model;
use BaseModel;

class EmailLog extends Model
{
	protected $table = 'email_log';

	public $fillable = [
		'name', 'from', 'to', 'subject', 'body', 'success'
	];

	public static function getLog($data)
	{
		$orderBy = 'id';
		$order = 'desc';
		$log = static::query();
		if(isset($data['filter_name']) && !empty($data['filter_name'])) {
			$log->where('name', '=', $data['filter_name']);
		}
		if(isset($data['filter_date']) && !empty($data['filter_date'])) {
			$log->where('created_at', '>=', $data['filter_date'])->where('created_at', '<=', $data['filter_date'].' 23:59:59');
		}

    	//$log = $log->orderBy($orderBy, $order)->get();
    	//$pagination = BaseModel::getPageData($log, $data, $orderBy);
    	$pagination = $log->orderBy($orderBy, $order)->paginate(BaseModel::$pagination);
		return $pagination;
    }
}
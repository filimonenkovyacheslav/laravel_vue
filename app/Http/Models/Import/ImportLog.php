<?php

namespace App\Http\Models\Import;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use Property;

class ImportLog extends Model
{
	public $timestamps = false;
	protected $table = 'import_log';

	public $fillable = [
		'run_id', 'import_id', 'entity_type', 'entity_id', 'result', 'message', 'date_added', 'time_added'
	];

	public static function getEntityTypes() {
		return [
			0 => '',
			1 => __('Property'),
			2 => __('File'),
		];
	}

	public static function getResults() {
		return [
			0 => __('Error'),
			1 => __('Inserted'),
			2 => __('Updated'),
			3 => __('Deleted'),
			5 => __('Info'),
		];
	}

	public static function addError($data)
	{
		$data['result'] = 0;
		$data['date_added'] = DB::raw('CURRENT_DATE');
		$data['time_added'] = DB::raw('CURRENT_TIME');
		$log = static::create($data);

		return $log ? $log->id : null;
	}

	public static function saveLog($data)
	{
		$data['date_added'] = DB::raw('CURRENT_DATE');
		$data['time_added'] = DB::raw('CURRENT_TIME');
		$log = static::create($data);

		return $log ? $log->id : null;
	}

	public static function getLogCounts($runId)
	{
		$select = '
			SUM(CASE WHEN entity_type=1 AND result=1 THEN 1 ELSE 0 END) as cnt_inserted,
			SUM(CASE WHEN entity_type=1 AND result=2 THEN 1 ELSE 0 END) as cnt_updated,
			SUM(CASE WHEN entity_type=1 AND result=3 THEN 1 ELSE 0 END) as cnt_deleted,
			SUM(CASE WHEN entity_type=2 AND result=1 THEN 1 ELSE 0 END) as files_added,
			SUM(CASE WHEN entity_type=2 AND result=3 THEN 1 ELSE 0 END) as files_deleted,
			SUM(CASE WHEN result=0 THEN 1 ELSE 0 END) as cnt_errors';
		$result = static::select(DB::raw($select))->where('run_id', $runId)->first();

		return $result ? $result->toArray() : [];
	}

	public static function getLogByRunId($runId, $logType = null)
	{
		$log = static::where('run_id', $runId);
		if(is_numeric($logType)) {
			if($logType == 0) {
				$log->where('result', 0);
			} else {
				$type = (string)$logType;
				if(strlen($type) == 2) {
					$log->where([['entity_type', substr($type, 0, 1)], ['result', substr($type, 1)]]);
				}
			}
		}
		$log = $log->orderBy('id', 'asc');
		$pagination = $log->paginate(30);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity);
		});
		return $pagination;
	}

	public static function _afterGet($log) {
		$logData = !is_array($log) ? $log->toArray() : $run;
		$logData['result_label'] = static::getResults()[$logData['result']];
		$logData['type_label'] = static::getEntityTypes()[$logData['entity_type']];
		if($logData['entity_type'] == 1 && $logData['entity_id']) {
			$logData['property_slug'] = Property::where('id', $logData['entity_id'])->value('slug');
		}
		
		return $logData;
	}
}
<?php

namespace App\Http\Models\Parsers;

use Illuminate\Database\Eloquent\Model;
use DB;
use BaseModel;
use Illuminate\Pagination\LengthAwarePaginator;

class Parser extends Model
{
	public $timestamps = false;

	public $fillable = [
		'model', 'url', 'status', 'log_id', 'status_time', 'last_start', 'last_end', 'last_result', 'all_results'
	];
	public static $statusJumping =[
		0 => [1, 5],
		1 => [2, 3, 4, 5],
		2 => [0, 3, 4, 5],
		3 => [4, 5],
		4 => [1, 5],
		5 => [1],
	];

	public static function getStatuses() {
		return [
			0 => __('Idle'),
			1 => __('Starting...'),
			2 => __('Active'),
			3 => __('Stopping...'),
			4 => __('Stopped'),
			5 => __('Error'),
		];
	}
	public static function canStatusJump($old, $new) {
		$jumps = static::$statusJumping;
		return isset($jumps[$old]) && in_array($new, $jumps[$old]);
	}
	
	public static function getParser($id) {
		$parser = Parser::find($id);
		return $parser ? $parser : null;
	}

	public static function updateResults($parser, $results = 1) {
		//dd($parser);
		$parser = is_numeric($parser) ? static::find($parser) : $parser;
		if(!$parser) return false;
		$parser->fill(['last_result' => $parser->last_result + $results, 'all_results' => $parser->all_results + $results])->save();
		return $parser;
	}

	public static function getTimestamp() {
		return DB::raw('now()');
	}

	public static function setStatus($id, $status, $data = []) {
		$parser = is_numeric($id) ? static::find($id) : null;
		if(!$parser) return false;
		if($parser->status == $status) return $parser;

		if(!static::canStatusJump($parser->status, $status)) return false;
		$timestamp = static::getTimestamp();
		$data['status'] = $status;
		$data['status_time'] = $timestamp;
		if($status == 0 || $status == 4 || $status == 5) {
			$data['last_end'] = $timestamp;
		} else if($status == 2) {
			$data['last_start'] = $timestamp;
		}
		if(isset($data['last_result'])) {
			$data['all_results'] = $data['last_result'] + $parser->all_results;
		}

		$parser->fill($data)->save();
		return $parser;
	}

	public static function setStartingStatus($id) {
		return static::setStatus($id, 1, ['log_id' => null, 'last_start' => null, 'last_end' => null, 'last_result' => null]);
	}

	public static function setStoppingStatus($id) {
		return static::setStatus($id, static::isParserInQuery($id) ? 3 : 4);
	}

	public static function getAll() {
		$pagination = static::select(['parsers.*', 'l.message'])->leftJoin('parser_log as l', 'l.id', '=', 'parsers.log_id')->orderBy('parsers.id')->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($parser) {
    		return static::_afterGet($parser);
		});
		return $pagination;
	}

	public static function _afterGet($parser)
	{
		if(!$parser || empty($parser)) return null;
		if(!is_null($parser['last_start'])) {
			$parser['last_start'] = date('d.m.Y H:i:s', strtotime($parser['last_start']));
		}
		return $parser;
	}

	public static function isParserInQuery($id)
	{
		//return true;
		return DB::table('jobs')->where('queue', 'parser'.$id)->count();
	}
}

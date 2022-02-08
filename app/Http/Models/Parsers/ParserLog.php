<?php

namespace App\Http\Models\Parsers;

use Illuminate\Database\Eloquent\Model;
use DB;

class ParserLog extends Model
{
	public $timestamps = false;
	protected $table = 'parser_log';

	public $fillable = [
		'parser_id', 'hash', 'url', 'method', 'params', 'entity_type', 'entity_id', 'result', 'message', 'date_added', 'time_added'
	];

	public static $entityTypes = [1 => 'user', 2 => 'property', 3 => 'image'];

	public static function saveError($id, $message, $data = []) {
		$data['parser_id'] = $id;
		$data['result'] = 0;
		$data['message'] = $message;
		$data['date_added'] = DB::raw('CURRENT_DATE');
		$data['time_added'] = DB::raw('CURRENT_TIME');
		$log = static::create($data);

		return $log ? $log->id : null;
	}

	public static function saveData($data) {
		if(!isset($data['result'])) {
			$data['result'] = 1;
		}
		//$data['hash'] = Hash::make($data['method'].$data['url'].$data['params']);
		$data['date_added'] = DB::raw('CURRENT_DATE');
		$data['time_added'] = DB::raw('CURRENT_TIME');
		static::create($data);
	}

	public static function isParsed($hash, $type, $parserId = false) {
	    $where = [['hash', $hash], ['entity_type', $type], ['result', 1]];
	    if ($parserId) {
	        array_push($where, ['parser_id', $parserId]);
        }
		$log = static::select('entity_id')->where($where)->first();
		return $log ? $log->entity_id : false;
	}

	public static function getLastError($id) {
		$log = static::select('id')->where([['parser_id', $id], ['result', 0]])->orderBy('id', 'desc')->first();
		return $log ? $log->id : false;
	}
	public static function saveUrlData($parser, $url, $data) {
		DB::statement('INSERT INTO parser_urls_log (parser_id, url, result) VALUES ('.$parser.",'".$url."','".pg_escape_string($data)."')");
	}
}

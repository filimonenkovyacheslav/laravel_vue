<?php

namespace App\Http\Models\Parsers;

use Illuminate\Database\Eloquent\Model;

class ParserResult extends Model
{
	protected $table = 'parser_results';

	public $fillable = [
		'parser_id', 'block', 'done', 'parsed', 'url', 'page'
	];
	
	public static function setResult($id, $block, $data = []) {
		$result = static::where([['parser_id', $id], ['block', $block]])->first();
		if(!$result) {
			$result = new static;
			$data['parser_id'] = $id;
			$data['block'] = $block;
		}
		$result->fill($data)->save();
		return $result ? $result->toArray() : null;
	}

	public static function getByParser($id, $done = null) {
		$results = static::where('parser_id', $id);
		if(!is_null($done)) {
			$results->where('done', $done);
		}
		$results = $results->get();
		return $results ? $results->toArray() : [];
	}

	public static function getParserResult($id, $block) {
		$done = static::where([['parser_id', $id], ['block', $block]])->value('done');

		return $done && $done == 1;
	}
	public static function getParserResults($id, $block) {
		$results = static::select('done', 'url', 'page')->where([['parser_id', $id], ['block', $block]])->first();

		return $results ? $results->toArray() : [];
	}
}

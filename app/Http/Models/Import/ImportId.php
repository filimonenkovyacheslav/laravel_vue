<?php

namespace App\Http\Models\Import;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use Property;

class ImportId extends Model
{
	protected $table = 'import_ids';

	public $fillable = [
		'user_id', 'import_id', 'entity_type', 'entity_id'
	];
	// entity_type: 1 - property, 2 - upload

	public static function getEntity($userId, $entityType, $importId)
	{
		$id = static::where([['user_id', $userId], ['entity_type', $entityType], ['import_id', $importId]])->value('entity_id');
		if(!$id) return false;

		if($entityType == 1) {
			$entity = Property::find($id);
			if($entity) {
				$entity = Property::_afterGet($entity);
				$entity['price'] = is_null($entity['price_local']) ? $entity['price_default'] : $entity['price_local'];
				foreach(Property::$hasArea as $name) {
					$localName = $name . '_local';
					$defaultName = $name . '_default';
					$entity[$name] = is_null($entity[$localName]) ? $entity[$defaultName] : $entity[$localName];
				}

				return $entity;
			}
		}
		return false;
	}

	public static function saveEntityId($userId, $entityType, $importId, $entityId)
	{
		$entity = static::where([['user_id', $userId], ['entity_type', $entityType], ['import_id', $importId]])->first();
		if($entity) {
			$entity->fill(['entity_id' => $entityId]);
			$entity->save();
		} else {
			$entity = static::create(['user_id' => $userId, 'entity_type' => $entityType, 'import_id' => $importId, 'entity_id' => $entityId]);
		}
		return $entity ? $entity->id : null;
	}
}

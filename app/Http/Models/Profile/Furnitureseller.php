<?php

namespace App\Http\Models\Profile;

use Illuminate\Database\Eloquent\Model;

class Furnitureseller extends \App\Http\Models\BaseModel
{
	public static $type = 'furnitureseller';
    
    protected $fillable = [
        'user_id',
        'lang_id',
        'description',
        'services',
        'furnitureseller_type'
    ];
    
    public static $tableName = 'furnituresellers';
    public static $key = 'user_id';
    
    public static $translatable = [
        'description'
    ];
    
    public static $selectable = [
        'description', 'furnitureseller_type'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public static function getFurnituresellerTypes() {
        return [
            'none' => ['id' => 0, 'label' => __('None'), 'color' => ''],
            'commercial' => ['id' => 1, 'label' => __('Commercial'), 'color' => 'orange'],
            'private' => ['id' => 2, 'label' => __('Private'), 'color' => 'blue'],
        ];
    }
    
    public static function getTypes($excepts = []) {
        $types = [];
        foreach (self::getFurnituresellerTypes() as $type) {
            if (in_array($type['id'], $excepts)) continue;
            
            $types[$type['id']] = $type['label'];
        }
        
        return $types;
    }
    
    public static function getCount()
    {
        return static::join('users', 'furnituresellers.user_id', '=', 'users.id')
            ->where([['users.status', 1], ['lang_id', static::getDefaultLang()]])
            ->count();
    }
    
    public static function _getFieldsList() {
        $fields = [
            'relation' => [
                'type' => [
                    'index' => 'furnitureseller_type',
                    'type' => 'selectbox',
                    'label' => __('Type'),
                    'value' => ['user', 'furnitureseller_type'],
                    'options' => self::getTypes()
                ],
                'description' => [
                    'index' => 'description',
                    'type' => 'tinymce',
                    'label' => __('Overview'),
                    'value' => ['user', 'description'],
                ],
            ],
        ];
        
        return $fields;
    }
}

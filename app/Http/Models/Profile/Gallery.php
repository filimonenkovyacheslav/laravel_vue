<?php

namespace App\Http\Models\Profile;

use Illuminate\Database\Eloquent\Model;

class Gallery extends \App\Http\Models\BaseModel
{
	public static $type = 'gallery';
    
    protected $fillable = [
        'user_id',
        'lang_id',
        'description',
        'services',
    ];
    
    public static $tableName = 'galleries';
    public static $key = 'user_id';
    
    public static $translatable = [
        'description'
    ];
    
    public static $selectable = [
        'description'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public static function getCount()
    {
        return static::join('users', self::$tableName. '.user_id', '=', 'users.id')
            ->where([['users.status', 1], ['lang_id', static::getDefaultLang()]])
            ->count();
    }
    
    public static function _getFieldsList() {
        $fields = [
            'relation' => [
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

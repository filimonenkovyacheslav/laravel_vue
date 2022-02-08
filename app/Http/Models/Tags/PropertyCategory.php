<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;
use BaseModel;

class PropertyCategory extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'property_category_id', 'lang_id', 'name', 'slug', 'parent_id',
	];

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return [
			'slug' => [
				'source' => 'name'
			]
		];
	}

	public static $listRoute = 'user.profile.propertyCategories';
	public static $type = 'propertyCategory';
	public static $tableName = 'property_categories';
	public static $key = 'property_category_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'property_category_id', 'slug', 'name', 'parent_id',
	];
    
    public static function countCategories() {
        return [
            [
                'title' => 'Categories',
                'count' => static::query()->count()
            ]
        ];
    }
    
    public static function getEntities($params = [], $orderBy = 'id', $withPagination = false, $one = false, $return = [])
    {
        $modelTable = static::$tableName;
        $tableKey = static::$key;
        
        $defPrefix = 'ef';
        $langPrefix = 'et';
        $defLang = BaseModel::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        $translatable = static::$translatable;
        
        $query = static::query();
        $query->orderBy($defPrefix. '.parent_id', 'ASC');
        
        if (empty($params)) {
            $query->where($defPrefix. '.parent_id', 0);
        }
        
        foreach($params as $k => $v) {
            if(!in_array($k, ['page', 'order_by'])) {
                if($k == 'whereIn' && is_array($v)) {
                    foreach($v as $whereKey => $whereVal) {
                        $query->whereIn($defPrefix.'.'.$whereKey, $whereVal);
                    }
                } else if($k == 'name') {
                    $query->where($k, 'ilike', '%'.$v.'%');
                } else {
                    $query->where((in_array($k, $translatable) ? $k : $defPrefix.'.'.$k), $v);
                }
            }
        }
        
        $query->from($modelTable.' as '.$defPrefix)
            ->select(DB::raw(BaseModel::getLangFieldsList(static::$selectable, $defPrefix, ($defLang == $langId ? [] : $translatable), $langPrefix)))
            ->where($defPrefix.'.lang_id', $defLang);
        
        if($defLang != $langId) {
            $query = static::replaceQuery($query, $translatable, $defPrefix, $langPrefix);
            
            $query->leftJoin($modelTable.' as '.$langPrefix, function ($join) use($defPrefix, $langPrefix, $langId, $tableKey){
                $join->on($langPrefix.'.'.$tableKey, '=', $defPrefix.'.'.$tableKey)
                    ->where($langPrefix.'.lang_id', '=', $langId);
            });
        }
        
        if(!empty($return)) {
            foreach($return as $k => $v) {
                $entities = $query->$k($v);
            }
        } else if($one) {
            $entities = $query->first();
        } else {
            if($withPagination) {
                $entities = $query->paginate(static::$pagination);
                $entities->getCollection()->transform(function ($entity) use ($params) {
                    return empty($params) ? static::_afterGet($entity) : BaseModel::_afterGet($entity);
                });
            } else {
                $entities = $query->get();
                $entities = $entities ? $entities->toArray() : null;
            }
        }
        
        return $entities;
    }
    
    public static function _afterGet($entity, $role = null) {
        $entityData = $entity;
    
        if(!empty($entityData)) {
            $entityData = !is_array($entityData) ? $entityData->toArray() : $entityData;
            $id = $entityData['property_category_id'];
            $entityData['children'] = static::getCategoriesHierarchy($id);
            $entityData['total_properties'] = static::countCategoryProperties($id);
            $entityData = static::_replaceLangFields($entityData);
        } else {
            $entityData = [];
        }
        
        return $entityData;
    }
    
    public static function _replaceLangFields($entity) {
        foreach(static::$translatable as $field) {
            $name = 'lang_'.$field;
            if(isset($entity[$name])) {
                $entity[$field] = $entity[$name];
            }
        }
        return $entity;
    }

	public static function getAllList($for_admin = false) {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.property_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM '. self::$tableName. ' as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN '. self::$tableName. ' as pl on (pl.property_category_id=p.property_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang.' ORDER BY p.parent_id';
		$entities = DB::select($query);

		$categories = [];
		foreach($entities as $p) {
            $categories[$p->property_category_id] = $p->name . ($for_admin ? ' (ID #' . $p->property_category_id . ')' : '');
		}
		return $categories;
	}
    
    public static function getCategoryById( $category_id )
    {
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $category = static::where([
            ['property_category_id', '=', $category_id],
            ['lang_id', '=', $langId]
        ])->first()->toArray();
        
        return $category;
    }
    
	public static function getCategoriesHierarchy( $parent_id = 0, $pre = '', $with_pre = false, $is_front = false, $deep = true, $filtered = false )
    {
//return [];
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();

        $entityQuery = $filtered ? static::$lastEntityQuery : false; 

//dd(static::$lastEntityQuery);
           
        $query = $entityQuery ? 'WITH filtered_properties AS (' . $entityQuery . ') ' : '';
                
        $query .= ' SELECT p.id, p.property_category_id, p.parent_id, ' . ($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)') . ' as name' .
            ( $entityQuery ? ', count(fa.id) as cnt_properties ' : '' ) .
            ' FROM property_categories as p';
        if ($entityQuery) {
            $query .= ' INNER JOIN property_category_relation as ar ON (ar.property_category_id=p.property_category_id) ' .
                ' INNER JOIN filtered_properties as fa ON (fa.id=ar.property_id)';
        }
        if($defLang != $langId) {
            $query .= ' LEFT JOIN property_categories as pl on (pl.property_category_id=p.property_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id=' . $parent_id . ' AND p.lang_id=' . $defLang;
        $query .= ( $entityQuery ? ' GROUP BY p.id' . ($defLang != $langId ? ',pl.name' : '') : '' ) . ' ORDER BY name';

        /*$query = 'SELECT p.id, p.property_category_id, p.parent_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM property_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN property_categories as pl on (pl.property_category_id=p.property_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';*/
//dd($entityQuery, static::$lastEntityQuery, $query);
        $categories = DB::select($query);
        
        $hierarchy = [];
        if (!empty($categories)) {
            $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
            foreach ($categories as $category) {
                $cat = (array)$category;
                $catId = $cat['property_category_id'];
                $propertiesCount = $entityQuery ? $cat['cnt_properties'] : self::countCategoryProperties($catId);
                $children = $deep ? static::getCategoriesHierarchy($catId, $preSend, $with_pre, $is_front, $deep, $filtered) : [];
                if ($is_front && !$propertiesCount && !count($children)) {
                    continue;
                }
                $cat['children'] = $children;
                $cat['total_properties'] = $propertiesCount;
                $cat['name'] = $pre . $cat['name'] . ($is_front ? ' (' . $propertiesCount . ')' : '');
                $hierarchy[] = $cat;
            }
        }
        
        return $hierarchy;
    }

	public static function getChildrenCategoriesByParentId($categoryId) {
		$query = 'SELECT property_category_id FROM property_categories WHERE parent_id = '.$categoryId;
		$entities = DB::select($query);
		$parent = array();
		foreach($entities as $p) {
            $parent[] = $p->property_category_id;
		}
		return $parent;
	}

	public static function getCategoryParentId($categoryId) {
		$query = 'SELECT parent_id FROM property_categories WHERE property_category_id = '.$categoryId;
		$entities = DB::select($query);
        $parent = '';
		foreach($entities as $p) {
            $parent = $p->parent_id;
		}
		return $parent;
	}

    public static function getSelectedCategoryParents($categoryId) {
        $defLang = static::getDefaultLang();
        $parents = [];
        $categories = self::query()
            ->where([
                ['property_category_id', '=', $categoryId],
                ['lang_id', '=', $defLang]
            ])->pluck('parent_id');
    
        $categories = !empty($categories) ? $categories->toArray() : [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if ($category) {
                    $parents[] = $category;
                    $parents = array_merge($parents, self::getSelectedCategoryParents($category));
                }
            }
        }
        
        return $parents;
    }

	public static function getAllListParent($parent_id = 0) {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.property_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM property_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN property_categories as pl on (pl.property_category_id=p.property_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.property_category_id';
		$entities = DB::select($query);

        $categories = [];
		foreach($entities as $p) {
            $propertiesCount = self::countCategoryProperties($p->property_category_id);
		    if ($propertiesCount) {
                $propertiesCount = ' (' . $propertiesCount . ')';
                $categories[$p->property_category_id] = $p->name;
            }
		}
		return $categories;
	}
    
    public static function getAllListHierarchy($parent_id = 0, $pre = '', $with_pre = false, $with_count = true) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $query = 'SELECT p.property_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM property_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN property_categories as pl on (pl.property_category_id=p.property_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';
        $entities = DB::select($query);
        
        $categories = [];
        $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
        foreach($entities as $p) {
            $propertiesCount = $with_count ? ' (' . self::countCategoryProperties($p->property_category_id) . ')' : '';
            /*$categories[$p->property_category_id] = $with_pre ? $pre . $p->name . $propertiesCount : $p->name . $propertiesCount;
            $categories = $categories + static::getAllListHierarchy($p->property_category_id, $preSend, $with_pre, $with_count);*/
            $categories[] = ['id' => $p->property_category_id, 'label' => $with_pre ? $pre . $p->name . $propertiesCount : $p->name . $propertiesCount];
            $categories = array_merge($categories, static::getAllListHierarchy($p->property_category_id, $preSend, $with_pre, $with_count));
        }
        return $categories;
    }

	public static function getPropertyCategoriesById($categoryId) {
        $categories = DB::table('property_categories')
			->where([
				['property_category_id', '=', $categoryId],
				['property_categories.lang_id', '=', static::getDefaultLang()],
			])
			->pluck('property_categories.property_category_id');
        $categories = !empty($categories) ? $categories->toArray() : [];
		return $categories;
	}

	public static function savePropertyCategories($property_id, $categories = []) {
       self::deletePropertyCategories($property_id);
       
		if(!empty($categories)) {
            $data = [];
            foreach ($categories as $category) {
                if (!is_numeric($category)) continue;
                $data[] = [
                    'property_id' => $property_id,
                    'property_category_id' => $category
                ];
            }
            DB::table('property_category_relation')->insert($data);
		}
		
		return self::getPropertyCategories($property_id);
	}
    
    public static function getPropertyCategories($property_id) {
	    $categories = DB::table('property_category_relation')
            ->where([
                ['property_id', '=', $property_id],
            ])->orderBy('property_category_id', 'ASC')->pluck('property_category_id');
    
        $categories = !empty($categories) ? $categories->toArray() : [];
    
        return $categories;
    }
    
    public static function getPropertyIdsByCategory($property_category_id) {
        $properties = DB::table('property_category_relation')
            ->where([
                ['property_category_id', '=', $property_category_id],
            ])->orderBy('property_id', 'ASC')->pluck('property_id');
    
        $properties = !empty($properties) ? $properties->toArray() : [];
        
        return $properties;
    }
    
    public static function countCategoryProperties($property_category_id) {
        $properties = DB::table('property_category_relation as r')
            ->join('properties as d', 'd.id', '=', 'r.property_id')
            ->where([
                ['r.property_category_id', '=', $property_category_id],
                ['d.status', '=', 1]
            ])->count();
        
        return $properties;
    }
    
    public static function beforeDeleteCategory($property_category_id) {
        self::deletePropertyCategoriesByCategory($property_category_id);
        
        $category = self::getCategoryById($property_category_id);
        $categoryParentId = $category['parent_id'];
    
        DB::table('property_categories')
            ->where([
                ['parent_id', '=', $property_category_id],
            ])
            ->update(['parent_id' => $categoryParentId]);
    }
    
    public static function deletePropertyCategories($property_id) {
        return DB::table('property_category_relation')
            ->where([
                ['property_id', '=', $property_id],
            ])->delete();
    }
    
    public static function deletePropertyCategoriesByCategory($property_category_id) {
        return DB::table('property_category_relation')
            ->where([
                ['property_category_id', '=', $property_category_id],
            ])->delete();
    }

	public static function _getFieldsList() {
		return [
			'property_category_id' => [
				'index' => 'property_category_id',
				'type' => 'hidden',
				'label' => __('Category Id'),
				'value' => ['propertyCategory', 'property_category_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Category Title *'),
				'value' => ['propertyCategory', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['propertyCategory', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['propertyCategory', 'parent_id'],
			],
		];
	}
    
    public static function _getPropertyFieldsList($with_count = true) {
        $fields = [
            'relation' => [
                'categories' => [
                    'index' => 'categories',
                    'type' => 'multiselectbox',
                    'label' => __('Categories'),
                    'value' => ['categories'],
                    'options' => self::getAllListHierarchy(0, '', true, $with_count)
                ],
            ],
        ];
        
        return $fields;
    }
}

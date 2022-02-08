<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;
use BaseModel;

class ArtCategory extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'art_category_id', 'lang_id', 'name', 'slug', 'parent_id',
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

	public static $listRoute = 'user.profile.artCategories';
	public static $type = 'artCategory';
	public static $tableName = 'art_categories';
	public static $key = 'art_category_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'art_category_id', 'slug', 'name', 'parent_id',
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
            $id = $entityData['art_category_id'];
            $entityData['children'] = static::getCategoriesHierarchy($id);
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

		$query = 'SELECT p.art_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM '. self::$tableName. ' as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN '. self::$tableName. ' as pl on (pl.art_category_id=p.art_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang.' ORDER BY p.parent_id';
		$entities = DB::select($query);

		$categories = [];
		foreach($entities as $p) {
            $categories[$p->art_category_id] = $p->name . ($for_admin ? ' (' . $p->art_category_id . ')' : '');
		}
		return $categories;
	}
 
    public static function getCategoriesHierarchy( $parent_id = 0, $pre = '', $with_pre = false, $is_front = false, $deep = true, $filtered = false )
    {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();

        $entityQuery = $filtered ? static::$lastEntityQuery : false; 
           
        $query = $entityQuery ? 'WITH filtered_arts AS (' . $entityQuery . ') ' : '';
                
        $query .= ' SELECT p.id, p.art_category_id, p.parent_id, ' . ($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)') . ' as name' .
            ( $entityQuery ? ', count(fa.id) as cnt_arts ' : '' ) .
            ' FROM art_categories as p';
        if ($entityQuery) {
            $query .= ' INNER JOIN art_category_relation as ar ON (ar.art_category_id=p.art_category_id) ' .
                ' INNER JOIN filtered_arts as fa ON (fa.id=ar.art_id)';
        }
        if($defLang != $langId) {
            $query .= ' LEFT JOIN art_categories as pl on (pl.art_category_id=p.art_category_id AND pl.lang_id=' . $langId . ')';
        }
        $query .= ' WHERE p.parent_id=' . $parent_id . ' AND p.lang_id=' . $defLang;
        $query .= ( $entityQuery ? ' GROUP BY p.id' . ($defLang != $langId ? ',pl.name' : '') : '' ) . ' ORDER BY name';
        //dd($query, $parent_id);
        $categories = DB::select($query);

        $hierarchy = [];
        if (!empty($categories)) {
            $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
            foreach ($categories as $category) {
                $cat = (array)$category;
                $catId = $cat['art_category_id'];
                $artsCount = $entityQuery ? $cat['cnt_arts'] : self::countCategoryArts($catId);
                $children = $deep ? static::getCategoriesHierarchy($catId, $preSend, $with_pre, $is_front, $deep, $filtered) : [];
                if ($is_front && !$artsCount && !count($children)) {
                    continue;
                }
                $cat['children'] = $children;
                $cat['total_arts'] = $artsCount;
                $cat['name'] = $pre . $cat['name'] . ($is_front ? ' (' . $artsCount . ')' : '');
                $hierarchy[] = $cat;
            }
        }
        
        return $hierarchy;
    }

	public static function getChildrenCategoriesByParentId($categoryId) {
		$query = 'SELECT art_category_id FROM '. self::$tableName. ' WHERE parent_id = '.$categoryId;
		$entities = DB::select($query);
		$parent = array();
		foreach($entities as $p) {
            $parent[] = $p->art_category_id;
		}
		return $parent;
	}

	public static function getCategoryParentId($categoryId) {
		$query = 'SELECT parent_id FROM '. self::$tableName. ' WHERE art_category_id = '.$categoryId;
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
                ['art_category_id', '=', $categoryId],
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

		$query = 'SELECT p.art_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM '. self::$tableName. ' as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN '. self::$tableName. ' as pl on (pl.art_category_id=p.art_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.art_category_id';
		$entities = DB::select($query);

        $categories = [];
		foreach($entities as $p) {
            $categories[$p->art_category_id] = $p->name;
		}
		return $categories;
	}
    
    public static function getAllListHierarchy($parent_id = 0, $pre = '', $with_pre = false) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $query = 'SELECT p.art_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM '. self::$tableName. ' as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN '. self::$tableName. ' as pl on (pl.art_category_id=p.art_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';
        $entities = DB::select($query);
        
        $categories = [];
        $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
        foreach($entities as $p) {
            $categories[] = ['id' => $p->art_category_id, 'label' =>  $with_pre ? $pre . $p->name : $p->name];
            $categories = array_merge($categories, static::getAllListHierarchy($p->art_category_id, $preSend, $with_pre));
        }
        return $categories;
    }

	public static function getArtCategoriesById($categoryId) {
        $categories = DB::table(self::$tableName)
			->where([
				['art_category_id', '=', $categoryId],
				['lang_id', '=', static::getDefaultLang()],
			])
			->pluck('art_category_id');
        $categories = !empty($categories) ? $categories->toArray() : [];
		return $categories;
	}
    
    public static function getCategoryById( $category_id )
    {
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $category = static::where([
            ['art_category_id', '=', $category_id],
            ['lang_id', '=', $langId]
        ])->first()->toArray();
        
        return $category;
    }

    public static function saveArtCategories($product_id, $categories = []) {
       self::deleteArtCategories($product_id);
       
		if(!empty($categories)) {
            $data = [];
            foreach ($categories as $category) {
                if (!is_numeric($category)) continue;
                $data[] = [
                    'art_id' => $product_id,
                    'art_category_id' => $category
                ];
            }
            DB::table('art_category_relation')->insert($data);
		}
		
		return self::getArtCategories($product_id);
	}
    
    public static function getArtCategories($product_id) {
	    $categories = DB::table('art_category_relation')
            ->where([
                ['art_id', '=', $product_id],
            ])->orderBy('art_category_id', 'ASC')->pluck('art_category_id');
    
        $categories = !empty($categories) ? $categories->toArray() : [];
	    
        return $categories;
    }
    public static function countCategoryArts($art_category_id) {
        $arts = DB::table('art_category_relation as r')
            ->join('arts as a', 'a.id', '=', 'r.art_id')
            ->where([
                ['r.art_category_id', '=', $art_category_id],
                ['a.status', '=', 1]
            ])->count();
        
        return $arts;
    }
    
    public static function deleteArtCategories($product_id) {
        return DB::table('art_category_relation')
            ->where([
                ['art_id', '=', $product_id],
            ])->delete();
    }
    
    public static function beforeDeleteCategory($category_id) {
        self::deleteArtCategoriesByCategory($category_id);
        
        $category = self::getCategoryById($category_id);
        $categoryParentId = $category['parent_id'];
        
        DB::table('art_categories')
            ->where([
                ['parent_id', '=', $category_id],
            ])
            ->update(['parent_id' => $categoryParentId]);
    }
    
    public static function deleteArtCategoriesByCategory($category_id) {
        return DB::table('art_category_relation')
            ->where([
                ['art_category_id', '=', $category_id],
            ])->delete();
    }

	public static function _getFieldsList() {
		return [
			'art_category_id' => [
				'index' => 'art_category_id',
				'type' => 'hidden',
				'label' => __('Category Id'),
				'value' => ['artCategory', 'art_category_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Category Title *'),
				'value' => ['artCategory', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['artCategory', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['artCategory', 'parent_id'],
			],
		];
	}
    
    public static function _getArtFieldsList() {
        $fields = [
            'relation' => [
                'categories' => [
                    'index' => 'categories',
                    'type' => 'multiselectbox',
                    'label' => __('Categories'),
                    'value' => ['categories'],
                    'options' => self::getAllListHierarchy(0, '', true)
                ],
            ],
        ];
        
        return $fields;
    }
}

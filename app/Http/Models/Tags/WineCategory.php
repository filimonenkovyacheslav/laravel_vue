<?php

namespace App\Http\Models\Tags;

use App\Http\Models\Wines\Wine;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;
use BaseModel;

class WineCategory extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'wine_category_id', 'lang_id', 'name', 'slug', 'parent_id', 'status'
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

	public static $listRoute = 'user.profile.wineCategories';
	public static $type = 'wineCategory';
	public static $tableName = 'wine_categories';
	public static $key = 'wine_category_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'wine_category_id', 'slug', 'name', 'parent_id', 'status'
	];
    
    public static function countCategories() {
        return [
            [
                'title' => 'Categories',
                'count' => static::query()->count(),
                'published' => static::query()->where('status', '=', 1)->count()
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
            $id = $entityData['wine_category_id'];
            $entityData['children'] = static::getCategoriesHierarchy($id);
            $entityData['total_wines'] = static::countCategoryWines($id);
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
    
    public static function setCategoryStatus($category_id, $status) {
        return self::where([
            ['wine_category_id', '=', $category_id],
        ])->update(['status' => $status]);
    }

	public static function getAllList($for_admin = false) {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.wine_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM wine_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN wine_categories as pl on (pl.wine_category_id=p.wine_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang. (!$for_admin ? ' AND p.status=1' : ''). ' ORDER BY p.parent_id';
		$entities = DB::select($query);

		$categories = [];
		foreach($entities as $p) {
            $categories[$p->wine_category_id] = $p->name . ($for_admin ? ' (ID #' . $p->wine_category_id . ')' : '');
		}
		return $categories;
	}
    
    public static function getCategoryById( $category_id )
    {
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $category = static::where([
            ['wine_category_id', '=', $category_id],
            ['lang_id', '=', $langId]
        ])->first()->toArray();
        
        return $category;
    }
    
	public static function getCategoriesHierarchy( $parent_id = 0, $pre = '', $with_pre = false, $is_front = false, $deep = true, $filtered = false )
    {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();

        $entityQuery = $filtered ? static::$lastEntityQuery : false; 
           
        $query = $entityQuery ? 'WITH filtered_wines AS (' . $entityQuery . ') ' : '';
                
        $query .= ' SELECT p.id, p.wine_category_id, p.parent_id, p.status, ' . ($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)') . ' as name' .
            ( $entityQuery ? ', count(fa.id) as cnt_wines ' : '' ) .
            ' FROM wine_categories as p';
        if ($entityQuery) {
            $query .= ' INNER JOIN wine_category_relation as ar ON (ar.wine_category_id=p.wine_category_id) ' .
                ' INNER JOIN filtered_wines as fa ON (fa.id=ar.wine_id)';
        }
        if ($defLang != $langId) {
            $query .= ' LEFT JOIN wine_categories as pl on (pl.wine_category_id=p.wine_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id=' . $parent_id . ' AND p.lang_id=' . $defLang;
        $query .= ( $entityQuery ? ' GROUP BY p.id' . ($defLang != $langId ? ',pl.name' : '') : '' ) . ' ORDER BY name';
      
        /*$query = 'SELECT p.id, p.wine_category_id, p.parent_id, p.status, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM wine_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN wine_categories as pl on (pl.wine_category_id=p.wine_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';*/
        $categories = DB::select($query);
        $hierarchy = [];
        if (!empty($categories)) {
            $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
            foreach ($categories as $category) {
                $cat = (array)$category;
                $catId = $cat['wine_category_id'];
                $winesCount = $entityQuery ? $cat['cnt_wines'] : self::countCategoryWines($catId);
                $children = $deep ? static::getCategoriesHierarchy($catId, $preSend, $with_pre, $is_front, $deep, $filtered) : [];

                if ($is_front && !$winesCount && !count($children) && !$cat['status']) {
                    continue;
                }
                if ($is_front && !$cat['status']) {
                    continue;
                }
                $cat['children'] = $children;
                $cat['total_wines'] = $winesCount;
                $cat['name'] = $pre . $cat['name'] . ($is_front ? ' (' . $winesCount . ')' : '');
                //$hierarchy[$catId] = $cat;
                $hierarchy[] = $cat;
            }
        }
        
        return $hierarchy;
    }

	public static function getChildrenCategoriesByParentId($categoryId, $status = false) {
		$query = 'SELECT wine_category_id FROM wine_categories WHERE parent_id = '.$categoryId . ($status !== false ? ' AND status='. $status : '');
		$entities = DB::select($query);
		$parent = array();
		foreach($entities as $p) {
            $parent[] = $p->wine_category_id;
		}
		return $parent;
	}

	public static function getCategoryParentId($categoryId) {
		$query = 'SELECT parent_id FROM wine_categories WHERE wine_category_id = '.$categoryId;
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
                ['status', '=', 1],
                ['wine_category_id', '=', $categoryId],
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

		$query = 'SELECT p.wine_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM wine_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN wine_categories as pl on (pl.wine_category_id=p.wine_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' AND p.status=1 ORDER BY p.wine_category_id';
		$entities = DB::select($query);

        $categories = [];
		foreach($entities as $p) {
            $winesCount = self::countCategoryWines($p->wine_category_id);
		    if ($winesCount) {
                $categories[$p->wine_category_id] = $p->name;
            }
		}
		return $categories;
	}
    
    public static function getAllListHierarchy($parent_id = 0, $pre = '', $with_pre = false) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $query = 'SELECT p.wine_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM wine_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN wine_categories as pl on (pl.wine_category_id=p.wine_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';
        $entities = DB::select($query);
        
        $categories = [];
        $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
        foreach($entities as $p) {
            $winesCount = ' (' . self::countCategoryWines($p->wine_category_id) . ')';
            $categories[$p->wine_category_id] = $with_pre ? $pre . $p->name . $winesCount : $p->name . $winesCount;
            $categories = $categories + static::getAllListHierarchy($p->wine_category_id, $preSend, $with_pre);
        }
        return $categories;
    }
    
    public static function getParentLevels( $parents = array(), $level = 0 ) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
    
        $parents = empty($parents) ? array(0) : $parents;
        $levelParents = $parents;
        $parents = '(' .implode(',', $parents) . ')';
        
        $query = 'SELECT p.parent_id, p.wine_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM wine_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN wine_categories as pl on (pl.wine_category_id=p.wine_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id IN '.$parents.' AND p.lang_id='.$defLang.' AND p.status=1 ORDER BY p.wine_category_id';
        $entities = DB::select($query);
        
        $levelId = $level;
        $levels = [
            $levelId => [
                'parents' => $levelParents,
                'categories' => []
            ]
        ];
        $newLevelParents = [];
        foreach($entities as $p) {
            if (!in_array($p->wine_category_id, $levelParents) && self::getChildrenCategoriesByParentId($p->wine_category_id, 1)) {
                $newLevelParents[] = $p->wine_category_id;
            }
            $levels[$levelId]['categories'][] = [
                'index' => $p->wine_category_id,
                'name' =>$p->name,
                'parent' => $p->parent_id
            ];
        }
        if (!empty($newLevelParents)) {
            $level++;
            $levels = array_merge($levels, self::getParentLevels($newLevelParents, $level));
        }
        
        return $levels;
    }

	public static function getWineCategoriesById($categoryId) {
        $categories = DB::table('wine_categories')
			->where([
				['wine_category_id', '=', $categoryId],
				['wine_categories.lang_id', '=', static::getDefaultLang()],
			])
			->pluck('wine_categories.wine_category_id');
        $categories = !empty($categories) ? $categories->toArray() : [];
		return $categories;
	}

	public static function saveWineCategories($wine_id, $categories = []) {
       self::deleteWineCategories($wine_id);
       
		if(!empty($categories)) {
            $data = [];
            foreach ($categories as $category) {
                if (!is_numeric($category)) continue;
                $data[] = [
                    'wine_id' => $wine_id,
                    'wine_category_id' => $category
                ];
            }
            DB::table('wine_category_relation')->insert($data);
		}
		
		return self::getWineCategories($wine_id);
	}
    
    public static function getWineCategories($wine_id) {
	    $categories = DB::table('wine_category_relation')
            ->where([
                ['wine_id', '=', $wine_id],
            ])->orderBy('wine_category_id', 'ASC')->pluck('wine_category_id');
    
        $categories = !empty($categories) ? $categories->toArray() : [];
    
        return $categories;
    }
    
    public static function getWineIdsByCategory($wine_category_id) {
        $wines = DB::table('wine_category_relation')
            ->where([
                ['wine_category_id', '=', $wine_category_id],
            ])->orderBy('wine_id', 'ASC')->pluck('wine_id');
    
        $wines = !empty($wines) ? $wines->toArray() : [];
        
        return $wines;
    }
    
    public static function setCategoryWinesStatus($category_id, $status) {
        $wineIds = self::getWineIdsByCategory($category_id);
        if (!empty($wineIds)) {
            Wine::query()->whereIn('id', $wineIds)->update(['status' => $status]);
            return true;
        }
        return false;
    }
    
    public static function countCategoryWines($wine_category_id) {
        $wines = DB::table('wine_category_relation as r')
            ->join('wines as p', 'p.id', '=', 'r.wine_id')
            ->where([
                ['r.wine_category_id', '=', $wine_category_id],
                ['p.status', '=', 1]
            ])->count();
        
        return $wines;
    }

    public static function deleteWineCategoriesById($id) {
        static::beforeDeleteCategory($id);
        static::where('wine_category_id', $id)->delete();
    }
    
    public static function beforeDeleteCategory($wine_category_id) {
        self::deleteWineCategoriesByCategory($wine_category_id);
        
        $category = self::getCategoryById($wine_category_id);
        $categoryParentId = $category['parent_id'];
    
        DB::table('wine_categories')
            ->where([
                ['parent_id', '=', $wine_category_id],
            ])
            ->update(['parent_id' => $categoryParentId]);
    }
    
    public static function deleteWineCategories($wine_id) {
        return DB::table('wine_category_relation')
            ->where([
                ['wine_id', '=', $wine_id],
            ])->delete();
    }
    
    public static function deleteWineCategoriesByCategory($wine_category_id) {
        return DB::table('wine_category_relation')
            ->where([
                ['wine_category_id', '=', $wine_category_id],
            ])->delete();
    }

	public static function _getFieldsList() {
		return [
			'wine_category_id' => [
				'index' => 'wine_category_id',
				'type' => 'hidden',
				'label' => __('Category Id'),
				'value' => ['wineCategory', 'wine_category_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Category Title *'),
				'value' => ['wineCategory', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['wineCategory', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['wineCategory', 'parent_id'],
			],
		];
	}
    
    public static function _getWineFieldsList() {
        $fields = [
            'relation' => [
                'categories' => [
                    'index' => 'categories',
                    'type' => 'multiselectbox',
                    'label' => __('Categories'),
                    'value' => ['categories'],
                    'options' => self::getParentLevels()
                ],
            ],
        ];
        
        return $fields;
    }
}

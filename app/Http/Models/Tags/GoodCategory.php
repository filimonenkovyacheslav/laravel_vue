<?php

namespace App\Http\Models\Tags;

use App\Http\Models\Goods\Good;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;
use BaseModel;

class GoodCategory extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'good_category_id', 'lang_id', 'name', 'slug', 'parent_id', 'status'
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

	public static $listRoute = 'user.profile.goodCategories';
	public static $type = 'goodCategory';
	public static $tableName = 'good_categories';
	public static $key = 'good_category_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'good_category_id', 'slug', 'name', 'parent_id', 'status'
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
            $id = $entityData['good_category_id'];
            $entityData['children'] = static::getCategoriesHierarchy($id);
            $entityData['total_goods'] = static::countCategoryGoods($id);
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
            ['good_category_id', '=', $category_id],
        ])->update(['status' => $status]);
    }

	public static function getAllList($for_admin = false) {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.good_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM good_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN good_categories as pl on (pl.good_category_id=p.good_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang. (!$for_admin ? ' AND p.status=1' : ''). ' ORDER BY p.parent_id';
		$entities = DB::select($query);

		$categories = [];
		foreach($entities as $p) {
            $categories[$p->good_category_id] = $p->name . ($for_admin ? ' (ID #' . $p->good_category_id . ')' : '');
		}
		return $categories;
	}
    
    public static function getCategoryById( $category_id )
    {
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $category = static::where([
            ['good_category_id', '=', $category_id],
            ['lang_id', '=', $langId]
        ])->first()->toArray();
        
        return $category;
    }
    
	public static function getCategoriesHierarchy( $parent_id = 0, $pre = '', $with_pre = false, $is_front = false, $deep = true, $filtered = false )
    {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();

        $entityQuery = $filtered ? static::$lastEntityQuery : false; 
           
        $query = $entityQuery ? 'WITH filtered_goods AS (' . $entityQuery . ') ' : '';
                
        $query .= ' SELECT p.id, p.good_category_id, p.parent_id, p.status, ' . ($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)') . ' as name' .
            ( $entityQuery ? ', count(fa.id) as cnt_goods ' : '' ) .
            ' FROM good_categories as p';
        if ($entityQuery) {
            $query .= ' INNER JOIN good_category_relation as ar ON (ar.good_category_id=p.good_category_id) ' .
                ' INNER JOIN filtered_goods as fa ON (fa.id=ar.good_id)';
        }
        if ($defLang != $langId) {
            $query .= ' LEFT JOIN good_categories as pl on (pl.good_category_id=p.good_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id=' . $parent_id . ' AND p.lang_id=' . $defLang;
        $query .= ( $entityQuery ? ' GROUP BY p.id' . ($defLang != $langId ? ',pl.name' : '') : '' ) . ' ORDER BY name';
      
        /*$query = 'SELECT p.id, p.good_category_id, p.parent_id, p.status, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM good_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN good_categories as pl on (pl.good_category_id=p.good_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';*/
        $categories = DB::select($query);
        $hierarchy = [];
        if (!empty($categories)) {
            $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
            foreach ($categories as $category) {
                $cat = (array)$category;
                $catId = $cat['good_category_id'];
                $goodsCount = $entityQuery ? $cat['cnt_goods'] : self::countCategoryGoods($catId);
                $children = $deep ? static::getCategoriesHierarchy($catId, $preSend, $with_pre, $is_front, $deep, $filtered) : [];

                if ($is_front && !$goodsCount && !count($children) && !$cat['status']) {
                    continue;
                }
                if ($is_front && !$cat['status']) {
                    continue;
                }
                $cat['children'] = $children;
                $cat['total_goods'] = $goodsCount;
                $cat['name'] = $pre . $cat['name'] . ($is_front ? ' (' . $goodsCount . ')' : '');
                //$hierarchy[$catId] = $cat;
                $hierarchy[] = $cat;
            }
        }
        
        return $hierarchy;
    }

	public static function getChildrenCategoriesByParentId($categoryId, $status = false) {
		$query = 'SELECT good_category_id FROM good_categories WHERE parent_id = '.$categoryId . ($status !== false ? ' AND status='. $status : '');
		$entities = DB::select($query);
		$parent = array();
		foreach($entities as $p) {
            $parent[] = $p->good_category_id;
		}
		return $parent;
	}

	public static function getCategoryParentId($categoryId) {
		$query = 'SELECT parent_id FROM good_categories WHERE good_category_id = '.$categoryId;
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
                ['good_category_id', '=', $categoryId],
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

		$query = 'SELECT p.good_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM good_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN good_categories as pl on (pl.good_category_id=p.good_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' AND p.status=1 ORDER BY p.good_category_id';
		$entities = DB::select($query);

        $categories = [];
		foreach($entities as $p) {
            $goodsCount = self::countCategoryGoods($p->good_category_id);
		    if ($goodsCount) {
                $categories[$p->good_category_id] = $p->name;
            }
		}
		return $categories;
	}
    
    public static function getAllListHierarchy($parent_id = 0, $pre = '', $with_pre = false) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $query = 'SELECT p.good_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM good_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN good_categories as pl on (pl.good_category_id=p.good_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';
        $entities = DB::select($query);
        
        $categories = [];
        $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
        foreach($entities as $p) {
            $goodsCount = ' (' . self::countCategoryGoods($p->good_category_id) . ')';
            $categories[$p->good_category_id] = $with_pre ? $pre . $p->name . $goodsCount : $p->name . $goodsCount;
            $categories = $categories + static::getAllListHierarchy($p->good_category_id, $preSend, $with_pre);
        }
        return $categories;
    }
    
    public static function getParentLevels( $parents = array(), $level = 0 ) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
    
        $parents = empty($parents) ? array(0) : $parents;
        $levelParents = $parents;
        $parents = '(' .implode(',', $parents) . ')';
        
        $query = 'SELECT p.parent_id, p.good_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM good_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN good_categories as pl on (pl.good_category_id=p.good_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id IN '.$parents.' AND p.lang_id='.$defLang.' AND p.status=1 ORDER BY p.good_category_id';
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
            if (!in_array($p->good_category_id, $levelParents) && self::getChildrenCategoriesByParentId($p->good_category_id, 1)) {
                $newLevelParents[] = $p->good_category_id;
            }
            $levels[$levelId]['categories'][] = [
                'index' => $p->good_category_id,
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

	public static function getGoodCategoriesById($categoryId) {
        $categories = DB::table('good_categories')
			->where([
				['good_category_id', '=', $categoryId],
				['good_categories.lang_id', '=', static::getDefaultLang()],
			])
			->pluck('good_categories.good_category_id');
        $categories = !empty($categories) ? $categories->toArray() : [];
		return $categories;
	}

	public static function saveGoodCategories($good_id, $categories = []) {
       self::deleteGoodCategories($good_id);
       
		if(!empty($categories)) {
            $data = [];
            foreach ($categories as $category) {
                if (!is_numeric($category)) continue;
                $data[] = [
                    'good_id' => $good_id,
                    'good_category_id' => $category
                ];
            }
            DB::table('good_category_relation')->insert($data);
		}
		
		return self::getGoodCategories($good_id);
	}
    
    public static function getGoodCategories($good_id) {
	    $categories = DB::table('good_category_relation')
            ->where([
                ['good_id', '=', $good_id],
            ])->orderBy('good_category_id', 'ASC')->pluck('good_category_id');
    
        $categories = !empty($categories) ? $categories->toArray() : [];
    
        return $categories;
    }
    
    public static function getGoodIdsByCategory($good_category_id) {
        $goods = DB::table('good_category_relation')
            ->where([
                ['good_category_id', '=', $good_category_id],
            ])->orderBy('good_id', 'ASC')->pluck('good_id');
    
        $goods = !empty($goods) ? $goods->toArray() : [];
        
        return $goods;
    }
    
    public static function setCategoryGoodsStatus($category_id, $status) {
        $goodIds = self::getGoodIdsByCategory($category_id);
        if (!empty($goodIds)) {
            Good::query()->whereIn('id', $goodIds)->update(['status' => $status]);
            return true;
        }
        return false;
    }
    
    public static function countCategoryGoods($good_category_id) {
        $goods = DB::table('good_category_relation as r')
            ->join('goods as p', 'p.id', '=', 'r.good_id')
            ->where([
                ['r.good_category_id', '=', $good_category_id],
                ['p.status', '=', 1]
            ])->count();
        
        return $goods;
    }

    public static function deleteGoodCategoriesById($id) {
        static::beforeDeleteCategory($id);
        static::where('good_category_id', $id)->delete();
    }
    
    public static function beforeDeleteCategory($good_category_id) {
        self::deleteGoodCategoriesByCategory($good_category_id);
        
        $category = self::getCategoryById($good_category_id);
        $categoryParentId = $category['parent_id'];
    
        DB::table('good_categories')
            ->where([
                ['parent_id', '=', $good_category_id],
            ])
            ->update(['parent_id' => $categoryParentId]);
    }
    
    public static function deleteGoodCategories($good_id) {
        return DB::table('good_category_relation')
            ->where([
                ['good_id', '=', $good_id],
            ])->delete();
    }
    
    public static function deleteGoodCategoriesByCategory($good_category_id) {
        return DB::table('good_category_relation')
            ->where([
                ['good_category_id', '=', $good_category_id],
            ])->delete();
    }

	public static function _getFieldsList() {
		return [
			'good_category_id' => [
				'index' => 'good_category_id',
				'type' => 'hidden',
				'label' => __('Category Id'),
				'value' => ['goodCategory', 'good_category_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Category Title *'),
				'value' => ['goodCategory', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['goodCategory', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['goodCategory', 'parent_id'],
			],
		];
	}
    
    public static function _getGoodFieldsList() {
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

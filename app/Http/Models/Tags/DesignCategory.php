<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;
use BaseModel;

class DesignCategory extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'design_category_id', 'lang_id', 'name', 'slug', 'parent_id',
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

	public static $listRoute = 'user.profile.designCategories';
	public static $type = 'designCategory';
	public static $tableName = 'design_categories';
	public static $key = 'design_category_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'design_category_id', 'slug', 'name', 'parent_id',
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
            $id = $entityData['design_category_id'];
            $entityData['children'] = static::getCategoriesHierarchy($id);
            $entityData['total_designs'] = static::countCategoryDesigns($id);
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

		$query = 'SELECT p.design_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM '. self::$tableName. ' as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN '. self::$tableName. ' as pl on (pl.design_category_id=p.design_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang.' ORDER BY p.parent_id';
		$entities = DB::select($query);

		$categories = [];
		foreach($entities as $p) {
            $categories[$p->design_category_id] = $p->name . ($for_admin ? ' (ID #' . $p->design_category_id . ')' : '');
		}
		return $categories;
	}
    
    public static function getCategoryById( $category_id )
    {
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $category = static::where([
            ['design_category_id', '=', $category_id],
            ['lang_id', '=', $langId]
        ])->first()->toArray();
        
        return $category;
    }
    
	public static function getCategoriesHierarchy( $parent_id = 0, $pre = '', $with_pre = false, $is_front = false, $deep = true, $filtered = false )
    {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();

        $entityQuery = $filtered ? static::$lastEntityQuery : false; 
           
        $query = $entityQuery ? 'WITH filtered_designs AS (' . $entityQuery . ') ' : '';
                
        $query .= ' SELECT p.id, p.design_category_id, p.parent_id, ' . ($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)') . ' as name' .
            ( $entityQuery ? ', count(fa.id) as cnt_designs ' : '' ) .
            ' FROM design_categories as p';
        if ($entityQuery) {
            $query .= ' INNER JOIN design_category_relation as ar ON (ar.design_category_id=p.design_category_id) ' .
                ' INNER JOIN filtered_designs as fa ON (fa.id=ar.design_id)';
        }
        if($defLang != $langId) {
            $query .= ' LEFT JOIN design_categories as pl on (pl.design_category_id=p.design_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id=' . $parent_id . ' AND p.lang_id=' . $defLang;
        $query .= ( $entityQuery ? ' GROUP BY p.id' . ($defLang != $langId ? ',pl.name' : '') : '' ) . ' ORDER BY name';


        /*$query = 'SELECT p.id, p.design_category_id, p.parent_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM design_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN design_categories as pl on (pl.design_category_id=p.design_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';*/
        $categories = DB::select($query);
        
        $hierarchy = [];
        if (!empty($categories)) {
            $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
            foreach ($categories as $category) {
                $cat = (array)$category;
                $catId = $cat['design_category_id'];
                $designsCount = $entityQuery ? $cat['cnt_designs'] : self::countCategoryDesigns($catId);
                $children = $deep ? static::getCategoriesHierarchy($catId, $preSend, $with_pre, $is_front, $deep, $filtered) : [];
                if ($is_front && !$designsCount && !count($children)) {
                    continue;
                }
                $cat['children'] = $children;
                $cat['total_designs'] = $designsCount;
                $cat['name'] = $pre . $cat['name'] . ($is_front ? ' (' . $designsCount . ')' : '');
                $hierarchy[] = $cat;
            }
        }
        
        return $hierarchy;
    }

	public static function getChildrenCategoriesByParentId($categoryId) {
		$query = 'SELECT design_category_id FROM design_categories WHERE parent_id = '.$categoryId;
		$entities = DB::select($query);
		$parent = array();
		foreach($entities as $p) {
            $parent[] = $p->design_category_id;
		}
		return $parent;
	}

	public static function getCategoryParentId($categoryId) {
		$query = 'SELECT parent_id FROM design_categories WHERE design_category_id = '.$categoryId;
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
                ['design_category_id', '=', $categoryId],
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

		$query = 'SELECT p.design_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM design_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN design_categories as pl on (pl.design_category_id=p.design_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.design_category_id';
		$entities = DB::select($query);

        $categories = [];
		foreach($entities as $p) {
            $designsCount = self::countCategoryDesigns($p->design_category_id);
		    if ($designsCount) {
                $designsCount = ' (' . $designsCount . ')';
                $categories[$p->design_category_id] = $p->name;
            }
		}
		return $categories;
	}
    
    public static function getAllListHierarchy($parent_id = 0, $pre = '', $with_pre = false, $with_count = true) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $query = 'SELECT p.design_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM design_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN design_categories as pl on (pl.design_category_id=p.design_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';
        $entities = DB::select($query);
        
        $categories = [];
        $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
        foreach($entities as $p) {
            $designsCount = $with_count ? ' (' . self::countCategoryDesigns($p->design_category_id) . ')' : '';
            /*$categories[$p->design_category_id] = $with_pre ? $pre . $p->name . $designsCount : $p->name . $designsCount;
            $categories = $categories + static::getAllListHierarchy($p->design_category_id, $preSend, $with_pre, $with_count);*/
            $categories[] = ['id' => $p->design_category_id, 'label' => $with_pre ? $pre . $p->name . $designsCount : $p->name . $designsCount];
            $categories = array_merge($categories, static::getAllListHierarchy($p->design_category_id, $preSend, $with_pre, $with_count));
        }
        return $categories;
    }

	public static function getDesignCategoriesById($categoryId) {
        $categories = DB::table('design_categories')
			->where([
				['design_category_id', '=', $categoryId],
				['design_categories.lang_id', '=', static::getDefaultLang()],
			])
			->pluck('design_categories.design_category_id');
        $categories = !empty($categories) ? $categories->toArray() : [];
		return $categories;
	}

	public static function saveDesignCategories($design_id, $categories = []) {
       self::deleteDesignCategories($design_id);
       
		if(!empty($categories)) {
            $data = [];
            foreach ($categories as $category) {
                if (!is_numeric($category)) continue;
                $data[] = [
                    'design_id' => $design_id,
                    'design_category_id' => $category
                ];
            }
            DB::table('design_category_relation')->insert($data);
		}
		
		return self::getDesignCategories($design_id);
	}
    
    public static function getDesignCategories($design_id) {
	    $categories = DB::table('design_category_relation')
            ->where([
                ['design_id', '=', $design_id],
            ])->orderBy('design_category_id', 'ASC')->pluck('design_category_id');
    
        $categories = !empty($categories) ? $categories->toArray() : [];
    
        return $categories;
    }
    
    public static function getDesignIdsByCategory($design_category_id) {
        $designs = DB::table('design_category_relation')
            ->where([
                ['design_category_id', '=', $design_category_id],
            ])->orderBy('design_id', 'ASC')->pluck('design_id');
    
        $designs = !empty($designs) ? $designs->toArray() : [];
        
        return $designs;
    }
    
    public static function countCategoryDesigns($design_category_id) {
        $designs = DB::table('design_category_relation as r')
            ->join('designs as d', 'd.id', '=', 'r.design_id')
            ->where([
                ['r.design_category_id', '=', $design_category_id],
                ['d.status', '=', 1]
            ])->count();
        
        return $designs;
    }
    
    public static function beforeDeleteCategory($design_category_id) {
        self::deleteDesignCategoriesByCategory($design_category_id);
        
        $category = self::getCategoryById($design_category_id);
        $categoryParentId = $category['parent_id'];
    
        DB::table('design_categories')
            ->where([
                ['parent_id', '=', $design_category_id],
            ])
            ->update(['parent_id' => $categoryParentId]);
    }
    
    public static function deleteDesignCategories($design_id) {
        return DB::table('design_category_relation')
            ->where([
                ['design_id', '=', $design_id],
            ])->delete();
    }
    
    public static function deleteDesignCategoriesByCategory($design_category_id) {
        return DB::table('design_category_relation')
            ->where([
                ['design_category_id', '=', $design_category_id],
            ])->delete();
    }

	public static function _getFieldsList() {
		return [
			'design_category_id' => [
				'index' => 'design_category_id',
				'type' => 'hidden',
				'label' => __('Category Id'),
				'value' => ['designCategory', 'design_category_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Category Title *'),
				'value' => ['designCategory', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['designCategory', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['designCategory', 'parent_id'],
			],
		];
	}
    
    public static function _getDesignFieldsList($with_count = true) {
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

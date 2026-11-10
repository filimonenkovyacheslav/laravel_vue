<?php

namespace App\Http\Models\Tags;

use App\Http\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use DB;
use Auth;
use BaseModel;

class ProductCategory extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'product_category_id', 'lang_id', 'name', 'slug', 'parent_id', 'status'
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

	public static $listRoute = 'user.profile.productCategories';
	public static $type = 'productCategory';
	public static $tableName = 'product_categories';
	public static $key = 'product_category_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'product_category_id', 'slug', 'name', 'parent_id', 'status'
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
            $id = $entityData['product_category_id'];
            $entityData['children'] = static::getCategoriesHierarchy($id);
            $entityData['total_products'] = static::countCategoryProducts($id);
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
            ['product_category_id', '=', $category_id],
        ])->update(['status' => $status]);
    }

	public static function getAllList($for_admin = false) {
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();

		$query = 'SELECT p.product_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM product_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN product_categories as pl on (pl.product_category_id=p.product_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.lang_id='.$defLang. (!$for_admin ? ' AND p.status=1' : ''). ' ORDER BY p.parent_id';
		$entities = DB::select($query);

		$categories = [];
		foreach($entities as $p) {
            $categories[$p->product_category_id] = $p->name . ($for_admin ? ' (ID #' . $p->product_category_id . ')' : '');
		}
		return $categories;
	}
    
    public static function getCategoryById( $category_id )
    {
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $category = static::where([
            ['product_category_id', '=', $category_id],
            ['lang_id', '=', $langId]
        ])->first()->toArray();
        
        return $category;
    }
    
	public static function getCategoriesHierarchy( $parent_id = 0, $pre = '', $with_pre = false, $is_front = false, $deep = true, $filtered = false )
    {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();

        $entityQuery = $filtered ? static::$lastEntityQuery : false; 
           
        $query = $entityQuery ? 'WITH filtered_products AS (' . $entityQuery . ') ' : '';
                
        $query .= ' SELECT p.id, p.product_category_id, p.parent_id, p.status, ' . ($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)') . ' as name' .
            ( $entityQuery ? ', count(fa.id) as cnt_products ' : '' ) .
            ' FROM product_categories as p';
        if ($entityQuery) {
            $query .= ' INNER JOIN product_category_relation as ar ON (ar.product_category_id=p.product_category_id) ' .
                ' INNER JOIN filtered_products as fa ON (fa.id=ar.product_id)';
        }
        if ($defLang != $langId) {
            $query .= ' LEFT JOIN product_categories as pl on (pl.product_category_id=p.product_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id=' . $parent_id . ' AND p.lang_id=' . $defLang;
        $query .= ( $entityQuery ? ' GROUP BY p.id' . ($defLang != $langId ? ',pl.name' : '') : '' ) . ' ORDER BY name';
      
        /*$query = 'SELECT p.id, p.product_category_id, p.parent_id, p.status, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM product_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN product_categories as pl on (pl.product_category_id=p.product_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= ' WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';*/
        $categories = DB::select($query);
        $hierarchy = [];
        if (!empty($categories)) {
            $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
            foreach ($categories as $category) {
                $cat = (array)$category;
                $catId = $cat['product_category_id'];
                $productsCount = $entityQuery ? $cat['cnt_products'] : self::countCategoryProducts($catId);
                $children = $deep ? static::getCategoriesHierarchy($catId, $preSend, $with_pre, $is_front, $deep, $filtered) : [];

                if ($is_front && !$productsCount && !count($children) && !$cat['status']) {
                    continue;
                }
                if ($is_front && !$cat['status']) {
                    continue;
                }
                $cat['children'] = $children;
                $cat['total_products'] = $productsCount;
                $cat['name'] = $pre . $cat['name'] . ($is_front ? ' (' . $productsCount . ')' : '');
                //$hierarchy[$catId] = $cat;
                $hierarchy[] = $cat;
            }
        }
        
        return $hierarchy;
    }

	public static function getChildrenCategoriesByParentId($categoryId, $status = false) {
		$query = 'SELECT product_category_id FROM product_categories WHERE parent_id = '.$categoryId . ($status !== false ? ' AND status='. $status : '');
		$entities = DB::select($query);
		$parent = array();
		foreach($entities as $p) {
            $parent[] = $p->product_category_id;
		}
		return $parent;
	}

	public static function getCategoryParentId($categoryId) {
		$query = 'SELECT parent_id FROM product_categories WHERE product_category_id = '.$categoryId;
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
                ['product_category_id', '=', $categoryId],
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

		$query = 'SELECT p.product_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM product_categories as p';
		if($defLang != $langId) {
			$query .= ' LEFT JOIN product_categories as pl on (pl.product_category_id=p.product_category_id AND pl.lang_id='.$langId.')';
		}
		$query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' AND p.status=1 ORDER BY p.product_category_id';
		$entities = DB::select($query);

        $categories = [];
		foreach($entities as $p) {
            $productsCount = self::countCategoryProducts($p->product_category_id);
		    if ($productsCount) {
                $categories[$p->product_category_id] = $p->name;
            }
		}
		return $categories;
	}
    
    public static function getAllListHierarchy($parent_id = 0, $pre = '', $with_pre = false) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        
        $query = 'SELECT p.product_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM product_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN product_categories as pl on (pl.product_category_id=p.product_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id = '.$parent_id.' AND p.lang_id='.$defLang.' ORDER BY p.parent_id';
        $entities = DB::select($query);
        
        $categories = [];
        $preSend = $with_pre ? $pre . '&nbsp;&nbsp;' : $pre;
        foreach($entities as $p) {
            $productsCount = ' (' . self::countCategoryProducts($p->product_category_id) . ')';
            $categories[$p->product_category_id] = $with_pre ? $pre . $p->name . $productsCount : $p->name . $productsCount;
            $categories = $categories + static::getAllListHierarchy($p->product_category_id, $preSend, $with_pre);
        }
        return $categories;
    }
    
    public static function getParentLevels( $parents = array(), $level = 0, $sort = 'p.product_category_id' ) {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
    
        $parents = empty($parents) ? array(0) : $parents;
        $levelParents = $parents;
        $parents = '(' .implode(',', $parents) . ')';
        
        $query = 'SELECT p.parent_id, p.product_category_id, '.($defLang == $langId ? 'p.name' : 'COALESCE(pl.name, p.name)').' as name FROM product_categories as p';
        if($defLang != $langId) {
            $query .= ' LEFT JOIN product_categories as pl on (pl.product_category_id=p.product_category_id AND pl.lang_id='.$langId.')';
        }
        $query .= '
		WHERE p.parent_id IN '.$parents.' AND p.lang_id='.$defLang.' AND p.status=1 ORDER BY ' . $sort;
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
            if (!in_array($p->product_category_id, $levelParents) && self::getChildrenCategoriesByParentId($p->product_category_id, 1)) {
                $newLevelParents[] = $p->product_category_id;
            }
            $levels[$levelId]['categories'][] = [
                'index' => $p->product_category_id,
                'name' =>$p->name,
                'parent' => $p->parent_id
            ];
        }
        if (!empty($newLevelParents)) {
            $level++;
            $levels = array_merge($levels, self::getParentLevels($newLevelParents, $level, $sort));
        }
        return $levels;
    }

	public static function getProductCategoriesById($categoryId) {
        $categories = DB::table('product_categories')
			->where([
				['product_category_id', '=', $categoryId],
				['product_categories.lang_id', '=', static::getDefaultLang()],
			])
			->pluck('product_categories.product_category_id');
        $categories = !empty($categories) ? $categories->toArray() : [];
		return $categories;
	}

	public static function saveProductCategories($product_id, $categories = []) {
       self::deleteProductCategories($product_id);
       
		if(!empty($categories)) {
            $data = [];
            foreach ($categories as $category) {
                if (!is_numeric($category)) continue;
                $data[] = [
                    'product_id' => $product_id,
                    'product_category_id' => $category
                ];
            }
            DB::table('product_category_relation')->insert($data);
		}
		
		return self::getProductCategories($product_id);
	}
    
    public static function getProductCategories($product_id) {
	    $categories = DB::table('product_category_relation')
            ->where([
                ['product_id', '=', $product_id],
            ])->orderBy('product_category_id', 'ASC')->pluck('product_category_id');
    
        $categories = !empty($categories) ? $categories->toArray() : [];
    
        return $categories;
    }
    
    public static function getProductIdsByCategory($product_category_id) {
        $products = DB::table('product_category_relation')
            ->where([
                ['product_category_id', '=', $product_category_id],
            ])->orderBy('product_id', 'ASC')->pluck('product_id');
    
        $products = !empty($products) ? $products->toArray() : [];
        
        return $products;
    }
    
    public static function setCategoryProductsStatus($category_id, $status) {
        $productIds = self::getProductIdsByCategory($category_id);
        if (!empty($productIds)) {
            Product::query()->whereIn('id', $productIds)->update(['status' => $status]);
            return true;
        }
        return false;
    }
    
    public static function countCategoryProducts($product_category_id) {
        $products = DB::table('product_category_relation as r')
            ->join('products as p', 'p.id', '=', 'r.product_id')
            ->where([
                ['r.product_category_id', '=', $product_category_id],
                ['p.status', '=', 1]
            ])->count();
        
        return $products;
    }

    public static function deleteProductCategoriesById($id) {
        static::beforeDeleteCategory($id);
        static::where('product_category_id', $id)->delete();
    }
    
    public static function beforeDeleteCategory($product_category_id) {
        self::deleteProductCategoriesByCategory($product_category_id);
        
        $category = self::getCategoryById($product_category_id);
        $categoryParentId = $category['parent_id'];
    
        DB::table('product_categories')
            ->where([
                ['parent_id', '=', $product_category_id],
            ])
            ->update(['parent_id' => $categoryParentId]);
    }
    
    public static function deleteProductCategories($product_id) {
        return DB::table('product_category_relation')
            ->where([
                ['product_id', '=', $product_id],
            ])->delete();
    }
    
    public static function deleteProductCategoriesByCategory($product_category_id) {
        return DB::table('product_category_relation')
            ->where([
                ['product_category_id', '=', $product_category_id],
            ])->delete();
    }

	public static function _getFieldsList() {
		return [
			'product_category_id' => [
				'index' => 'product_category_id',
				'type' => 'hidden',
				'label' => __('Category Id'),
				'value' => ['productCategory', 'product_category_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Category Title *'),
				'value' => ['productCategory', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['productCategory', 'slug'],
			],
			'parent_id' => [
				'index' => 'parent_id',
				'type' => 'text',
				'label' => __('Parent ID'),
				'value' => ['productCategory', 'parent_id'],
			],
		];
	}
    
    public static function _getProductFieldsList() {
        $fields = [
            'relation' => [
                'categories' => [
                    'index' => 'categories',
                    'type' => 'multiselectbox',
                    'label' => __('Categories'),
                    'value' => ['categories'],
                    'options' => self::getParentLevels(array(), 0, 'name')
                ],
            ],
        ];
        
        return $fields;
    }
}

<?php

namespace App\Http\Models\MenuCategoryItem;

use Illuminate\Database\Eloquent\Model;

class MenuCategoryItem extends \App\Http\Models\BaseModel
{
	protected $table = 'menu_category_items';
    protected $fillable = ['slug', 'label'];
}
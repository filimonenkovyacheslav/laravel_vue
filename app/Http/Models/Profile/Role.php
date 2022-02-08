<?php

namespace App\Http\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Role extends Model
{
	public $fillable = [
		'name', 'title', 'description',
	];

    public function users()
	{
	  return $this->hasMany(User::class);
	}
}

<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadProduct extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_products';
        
        protected $fillable = [
            'product_id',
            'upload_id',
        ];
    }

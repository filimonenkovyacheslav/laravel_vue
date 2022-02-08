<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadFurniture extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_furnitures';
        
        protected $fillable = [
            'furniture_id',
            'upload_id',
        ];
    }

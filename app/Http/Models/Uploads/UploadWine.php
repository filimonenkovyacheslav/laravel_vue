<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadWine extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_wines';
        
        protected $fillable = [
            'wine_id',
            'upload_id',
        ];
    }

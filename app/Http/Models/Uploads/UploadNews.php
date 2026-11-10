<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadNews extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_news';
        
        protected $fillable = [
            'news_id',
            'upload_id',
        ];
    }

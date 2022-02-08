<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadArt extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_arts';
        
        protected $fillable = [
            'art_id',
            'upload_id',
        ];
    }

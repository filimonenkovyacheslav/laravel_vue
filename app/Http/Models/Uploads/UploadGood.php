<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadGood extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_goods';
        
        protected $fillable = [
            'good_id',
            'upload_id',
        ];
    }

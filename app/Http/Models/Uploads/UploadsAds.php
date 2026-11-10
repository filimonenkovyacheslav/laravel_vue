<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadsAds extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_ads';
        
        protected $fillable = [
            'ads_id',
            'upload_id',
        ];
    }

<?php
    
    namespace App\Http\Models\Uploads;
    
    use Illuminate\Database\Eloquent\Model;
    use Cviebrock\EloquentSluggable\Sluggable;
    
    class UploadDesign extends Model
    {
        public $timestamps = false;
        
        protected $table = 'uploads_designs';
        
        protected $fillable = [
            'design_id',
            'upload_id',
        ];
    }

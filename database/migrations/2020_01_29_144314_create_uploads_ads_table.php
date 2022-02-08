<?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class CreateUploadsAdsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            if (!Schema::hasTable('uploads_ads')) {
                Schema::create('uploads_ads', function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('ads_id');
                    $table->integer('upload_id');
                    $table->index(['ads_id', 'upload_id']);
                });
            }
        }
        
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('uploads_ads');
        }
    }

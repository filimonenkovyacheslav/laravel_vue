<?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class CreateAdsKeywordsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            if (!Schema::hasTable('ads_keywords')) {
                Schema::create('ads_keywords', function (Blueprint $table) {
                    $table->increments('keyword_id');
                    $table->string('name', 500)->nullable();
                    $table->integer('ads_id');
                    $table->string('key_hash', 32)->nullable();
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
            Schema::dropIfExists('ads_keywords');
        }
    }

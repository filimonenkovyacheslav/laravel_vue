<?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class CreateAdsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            if (!Schema::hasTable('ads')) {
                Schema::create('ads', function (Blueprint $table) {
                    $table->increments('ads_id');
                    $table->string('title', 500)->nullable();
                    $table->string('url', 500)->nullable();
                    $table->text('keywords')->nullable();
                    $table->string('file_link', 500)->nullable();
                    $table->integer('country_id')->nullable();
                    $table->string('city', 200)->nullable();
                    $table->string('state', 200)->nullable();
                    $table->string('address', 500)->nullable();
                    $table->string('type', 100)->nullable();
                    $table->integer('order')->nullable();
                    $table->integer('status')->nullable();
                    $table->timestamps();
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
            Schema::dropIfExists('ads');
        }
    }

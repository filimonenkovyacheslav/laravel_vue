<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuCategoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_category_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 50);
            $table->string('label', 50);           
            $table->timestamps();
        });

        $items = [
            [ 
                'slug' => 'professional',
                'label' => 'Professionals'
            ],
            [ 
                'slug' => 'brand',
                'label' => 'Brands'
            ],
            [ 
                'slug' => 'product',
                'label' => 'Products'
            ],
            [ 
                'slug' => 'good',
                'label' => 'Marketplace'
            ],           
            [ 
                'slug' => 'news',
                'label' => 'News'
            ]
        ];

        DB::table('menu_category_items')->insert($items);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_category_items');
    }
}

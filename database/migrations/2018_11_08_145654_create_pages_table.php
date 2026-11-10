<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->tinyInteger('lang_id');
            $table->text('content');
            $table->timestamps();
            $table->unique(['name', 'lang_id']);
        });
        $pages = [
            ['name' => 'terms','lang_id' => 0, 'content' => 'Terms & Privacy'],
            ['name' => 'imprint','lang_id' => 0, 'content' => 'Imprint'],
        ];

        foreach($pages as $p) {
            $page = new Page();
            $page->fill($p);
            $page->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}

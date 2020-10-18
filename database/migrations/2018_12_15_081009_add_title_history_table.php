<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('titles_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('title_id');
            $table->integer('user_id');
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('predecessor')->nullable();
            $table->string('description')->nullable();
            $table->string('volume')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('titles_history');
    }
}

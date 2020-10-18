<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from')->references('id')->on('api_users')->onDelete('restrict');
            $table->integer('to')->references('id')->on('api_users')->onDelete('restrict');
            $table->string('title')->references('name')->on('titles')->onDelete('restrict');
            $table->string('what');

            $table->integer('owner')
                ->references('id')->on('api_users')->onDelete('restrict');

            $table->boolean('is_new')->default(false);
            $table->boolean('is_attachment_exist')->default(false);

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
        Schema::dropIfExists('logs');
    }
}

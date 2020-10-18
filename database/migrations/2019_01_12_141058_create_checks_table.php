<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Http\Controllers\SettingsController;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('file_id')
                ->nullable()
                ->references('id')->on('checked_files')->onDelete('restrict');
            $table->string('filename');
            $table->integer('status');
            $table->integer('mistake_count');
            $table->integer('owner')
                ->references('id')->on('api_users')->onDelete('restrict');

            $table->string('extension')->default('');

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
        Schema::dropIfExists('checks');
    }
}
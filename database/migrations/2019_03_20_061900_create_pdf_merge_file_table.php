<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Http\Controllers\SettingsController;

class CreatePdfMergeFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_merge_files', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('owner')
                ->references('id')->on('api_users')->onDelete('restrict');

            $table->integer('drop_id');
            $table->string('drop_uin');
            $table->string('original_name');

            $table->integer('size');
            $table->string('server_name');
            $table->string('uin');

            $table->boolean('is_name')->default(false);

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
        Schema::dropIfExists('pdf_merge_files');
    }
}

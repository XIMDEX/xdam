<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMediaConversionIdOnPendingVideoCompressionTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pending_video_compression_tasks', function (Blueprint $table) {
            $table->string('media_conversion_name_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pending_video_compression_tasks', function (Blueprint $table) {
            $table->dropColumn('media_conversion_name_id');
        });
    }
}

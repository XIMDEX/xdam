<?php

use App\Enums\YesNo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("solr_id");
            $table->string("dam_path");
            $table->string("crawler_path");
            $table->string("filename");
            $table->string("extension");
            $table->string("type");
            $table->string("mime_type");
            $table->string("hash");
            $table->string("uri");
            $table->string("encoding");
            $table->string("content_type");
            $table->bigInteger("size");
            $table->longText("metadata");
            $table->string("origin");
            $table->string("crawler_job_id");
            $table->tinyInteger('in_dam_index')->unsigned()->default(YesNo::No);
            $table->timestamp('indexed_at')->nullable();
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
        Schema::dropIfExists('files');
    }
}

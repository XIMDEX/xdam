<?php

use App\Enums\CrawlerJobStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrawlerJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawler_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('numfilesinindex');
            $table->string('first_indexed_at');
            $table->string('last_indexed_at');
            $table->integer('numfilesprocessed')->default(0);
            $table->tinyInteger('status')->unsigned()->default(CrawlerJobStatus::Created);
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
        Schema::dropIfExists('crawler_jobs');
    }
}

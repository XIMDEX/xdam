<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCdnHashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdn_hashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cdn_id')->references('id')->on('cdn')->onDelete('cascade');
            $table->foreignUuid('resource_id')->references('id')->on('dam_resources')->onDelete('cascade');
            $table->unsignedBigInteger('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->string('resource_hash')->nullable(false)->unique();
            $table->unsignedBigInteger('total_visits')->nullable(false)->default(0);
            $table->timestamp('last_visited_at')->nullable(true)->default(null);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cdn_hashes');
    }
}

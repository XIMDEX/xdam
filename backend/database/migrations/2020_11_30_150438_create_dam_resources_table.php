<?php

use App\Enums\ResourceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDamResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dam_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("external_id")->nullable();
            $table->string("name")->nullable();
            $table->enum('type', ResourceType::getValues())
                ->default(ResourceType::document);
            $table->json("data")->nullable();
            $table->integer('collection_id')->nullable();
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
        Schema::dropIfExists('dam_resources');
    }
}

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
            $table->string('name')->nullable();
            $table->enum('type', ResourceType::getValues())
                ->default(ResourceType::document);
            $table->json("data")->nullable();
            $table->timestamps();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreign('model_id', 'media_dam_fk')
                ->references('id')
                ->on('dam_resources')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign('media_dam_fk');
        });

        Schema::dropIfExists('dam_resources');
    }
}

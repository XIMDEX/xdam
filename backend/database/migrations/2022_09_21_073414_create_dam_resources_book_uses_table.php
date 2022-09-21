<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDamResourcesBookUsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dam_resources_renders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('resource_id')->references('id')
                ->on('dam_resources')->onDelete('cascade');
            $table->string('remote_key');
            $table->unsignedBigInteger('total_renders')->nullable(false)->default(1);
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
        Schema::dropIfExists('dam_resources_renders');
    }
}

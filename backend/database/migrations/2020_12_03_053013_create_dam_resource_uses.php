<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDamResourceUses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dam_resource_uses', function (Blueprint $table) {
            $table->id();
            $table->uuid('dam_resource_id');
            $table->string("used_in")->nullable();
            $table->string("related_to")->nullable();
            $table->timestamps();
            $table->foreign('dam_resource_id')
                ->references('id')
                ->on('dam_resources')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dam_resource_uses');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FkResourceLomes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resource_lomes', function (Blueprint $table) {
            $table->foreign('dam_resource_id', 'reource_lomes_fk')
                ->constrained('dam_resources')
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
        Schema::table('resource_lomes', function (Blueprint $table) {
            $table->dropForeign('reource_lomes_fk');
        });
    }
}

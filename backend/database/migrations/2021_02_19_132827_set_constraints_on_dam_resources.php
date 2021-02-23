<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetConstraintsOnDamResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //it's in different migration, because requires the table "collections" created
        Schema::table('dam_resources', function (Blueprint $table) {
            $table->foreign('collection_id', 'dam_resource_collection_fk')->references('id')->on('collections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

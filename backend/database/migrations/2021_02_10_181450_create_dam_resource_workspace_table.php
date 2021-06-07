<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDamResourceWorkspaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dam_resource_workspace', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('workspace_id');
            $table->uuid('dam_resource_id')->nullable();
            $table->timestamps();

            $table->foreign('dam_resource_id', 'dam_resource_workspaces_fk')
                ->references('id')
                ->on('dam_resources')
                ->onDelete('cascade');

            $table->foreign('workspace_id', 'workspaces_dam_resource_fk')
                ->references('id')
                ->on('workspaces')
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
        Schema::dropIfExists('dam_resource_workspace');
    }
}

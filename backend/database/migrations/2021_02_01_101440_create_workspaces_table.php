<?php

use App\Enums\WorkspaceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkspacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workspaces', function (Blueprint $table) {
            //$table->uuid('id')->primary();
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('organization_id');
            $table->string('type')->default(WorkspaceType::generic);
            $table->timestamps();
            $table->foreign('organization_id', 'workspaces_organization_id_fk')->references('id')->on('organizations')->onDelete('cascade');
        });





    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workspaces');
    }
}

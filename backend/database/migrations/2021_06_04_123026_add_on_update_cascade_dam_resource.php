<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnUpdateCascadeDamResource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //$this->update_table('dam_resource_workspace', 'dam_resource_id', $current_resource_id, $new_id);
        Schema::table('dam_resource_workspace', function (Blueprint $table) {

            $table->dropForeign('dam_resource_workspaces_fk');
            $table->dropForeign('workspaces_dam_resource_fk');

            $table->foreign('dam_resource_id', 'dam_resource_workspaces_fk')
                ->references('id')
                ->on('dam_resources')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('workspace_id', 'workspaces_dam_resource_fk')
                ->references('id')
                ->on('workspaces')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });


        //$this->update_table('category_dam_resource', 'dam_resource_id', $current_resource_id, $new_id);
        Schema::table('category_dam_resource', function (Blueprint $table) {

            $table->dropForeign('category_dam_resource_category_id_foreign');
            $table->dropForeign('category_dam_resource_dam_resource_id_foreign');

            $table->foreign('dam_resource_id', 'category_dam_resource_category_id_foreign')
                ->references('id')
                ->on('dam_resources')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('category_id', 'category_dam_resource_dam_resource_id_foreign')
                ->references('id')
                ->on('categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        //$this->update_table('dam_resource_uses', 'dam_resource_id', $current_resource_id, $new_id);
        Schema::table('dam_resource_uses', function (Blueprint $table) {

            $table->dropForeign('dam_resource_uses_dam_resource_id_foreign');

            $table->foreign('dam_resource_id', 'dam_resource_uses_dam_resource_id_foreign')
                ->references('id')
                ->on('dam_resources')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        //$this->update_table('media', 'model_id', $current_resource_id, $new_id);
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
        //$this->update_table('dam_resource_workspace', 'dam_resource_id', $current_resource_id, $new_id);
        Schema::table('dam_resource_workspace', function (Blueprint $table) {

            $table->dropForeign('dam_resource_workspaces_fk');
            $table->dropForeign('workspaces_dam_resource_fk');

            $table->foreign('dam_resource_id', 'dam_resource_workspaces_fk')
                ->references('id')
                ->on('dam_resources')
                ->onDelete('cascade');

            $table->foreign('workspace_id', 'workspaces_dam_resource_fk')
                ->references('id')
                ->on('workspaces')
                ->onDelete('cascade');
        });


        //$this->update_table('category_dam_resource', 'dam_resource_id', $current_resource_id, $new_id);
        Schema::table('category_dam_resource', function (Blueprint $table) {

            $table->dropForeign('category_dam_resource_category_id_foreign');
            $table->dropForeign('category_dam_resource_dam_resource_id_foreign');

            $table->foreign('dam_resource_id', 'category_dam_resource_category_id_foreign')
                ->references('id')
                ->on('dam_resources')
                ->onDelete('cascade');

            $table->foreign('category_id', 'category_dam_resource_dam_resource_id_foreign')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });

        //$this->update_table('dam_resource_uses', 'dam_resource_id', $current_resource_id, $new_id);
        Schema::table('dam_resource_uses', function (Blueprint $table) {

            $table->dropForeign('dam_resource_uses_dam_resource_id_foreign');

            $table->foreign('dam_resource_id', 'dam_resource_uses_dam_resource_id_foreign')
                ->references('id')
                ->on('dam_resources')
                ->onDelete('cascade');
        });

        //$this->update_table('media', 'model_id', $current_resource_id, $new_id);
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign('media_dam_fk');
        });
    }
}

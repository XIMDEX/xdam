<?php

use App\Enums\AccessPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessPermissionRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_permission_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('access_permission_id');
            $table->string('rule')->nullable(false);
            $table->enum('rule_type', AccessPermission::getValues())
                ->default(AccessPermission::default);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('access_permission_id')
                ->references('id')
                ->on('access_permissions')
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
        Schema::dropIfExists('access_permission_rules');
    }
}

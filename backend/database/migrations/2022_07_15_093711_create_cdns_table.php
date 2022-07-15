<?php

use App\Enums\AccessPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCdnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('access_permission', AccessPermission::getValues())
                    ->default(AccessPermission::default);
            $table->json('access_permission_properties')->default("{}");
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
        Schema::dropIfExists('cdns');
    }
}

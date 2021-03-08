<?php

use App\Enums\ResourceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDataTypeDamResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'dam_resources',
            function (Blueprint $table) {
                $table->enum('type', ResourceType::getValues())
                    ->default(ResourceType::document)->change();
            }
        );

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

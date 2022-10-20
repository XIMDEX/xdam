<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationCdnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_cdn', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cdn_id')->nullable(false);
            $table->unsignedInteger('organization_id')->nullable(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('cdn_id')->references('id')
                ->on('cdns')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')
                ->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_cdn');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcceptsResourceTypeOnCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('collections', 'type_id')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('type_id');
            });
        }
        Schema::table('collections', function (Blueprint $table) {
            $table->string('accept')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('accept');
        });
    }
}

<?php

use App\Models\Corporation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableCorporationAddColumnDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corporation', function(Blueprint $table) {
            $table->boolean('is_default')->after('type')->default(false);
        });

        $corporation = Corporation::find(1);

        $corporation->is_default = true;
        $corporation->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corporation', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}

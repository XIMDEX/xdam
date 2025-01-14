<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddUuidToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the UUID column, make sure it's nullable initially to avoid errors
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Update existing records with UUIDs
        \DB::table('users')->get()->each(function ($user) {
            \DB::table('users')->where('id', $user->id)->update([
                'uuid' => Str::uuid()
            ]);
        });

        // Now that all users have UUIDs, update the column to be non-nullable
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}
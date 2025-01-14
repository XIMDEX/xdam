<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Add the uuid field which is unique and nullable initially
            if (!Schema::hasColumn('organizations', 'uuid')) {
                $table->uuid('uuid')->nullable();
            }
        });

        // Update existing records with UUIDs
        \DB::table('organizations')->whereNull('uuid')->get()->each(function ($organization) {
            \DB::table('organizations')
                ->where('id', $organization->id)
                ->update(['uuid' => Str::uuid()]);
        });

        // Now that all users have UUIDs, update the column to be non-nullable
        // and add the unique constraint if it does not exist
        Schema::table('organizations', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
            // Check before adding the unique index
            if (!Schema::hasColumn('organizations', 'uuid')) {
                $table->unique('uuid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};

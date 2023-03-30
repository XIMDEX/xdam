<?php

use App\Models\Corporation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCorporations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporation', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false)->unique();
            $table->string('description')->nullable(true);
            $table->string('type')->default('course');
            $table->timestamps();
        });

        Corporation::create([
            'id' => 1,
            'name' => 'Public',
            'description' => 'Common organization',
            'type' => 'course'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_corporations');
    }
}

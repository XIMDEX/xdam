<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterTablelomes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resource_lomes', function (Blueprint $table) {

            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
            '&#225;'=>'a', '&#233;'=>'e', '&#237;'=>'i', '&#243;'=>'o', '&#250;'=>'u',
            '&#193;'=>'A', '&#201;'=>'E', '&#205;'=>'I', '&#211;'=>'O', '&#218;'=>'U',
            '&#209;'=>'N', '&#241;'=>'n' );

            $path = storage_path('lomes/coreJson');
            $json_file = file_get_contents($path . '/lomesSchema_core.json');
            $schemas = json_decode($json_file, true);

            // $table->id();
            // $table->uuid('dam_resource_id');
            $schemaOutput = $schemas;
            $column_before = '';
            foreach ($schemas["tabs"] as $key => $tab) {
                $db_field_key = strtolower($tab['title'] . '_' .$tab["key"]);
                foreach ($tab['properties'] as $label => $props) {
                    $db_field_prop = strtr( str_replace(' ', '_', strtolower($db_field_key . '_' . $label)), $unwanted_array );
                    $schemaOutput["tabs"][$key]["properties"][$label]["data_field"] = $db_field_prop;

                    $method = ($props["type"] == "json" || $props["type"] == "array" || $props["type"] == "object") ? "jsonb" : $props["type"];
                    if (!method_exists($table, $method)) {
                        $method = 'string';
                    }
                    $existColumn = Schema::hasColumn($table->getTable(), $db_field_prop);
                    // if ($existColumn) {
                    //     $columnType = DB::getSchemaBuilder()->getColumnType($table, $db_field_prop);
                    // }

                    if (!$existColumn) {
                        if ($column_before === '') {
                            $table->$method($db_field_prop)->nullable()->first();
                        } else {
                            $table->$method($db_field_prop)->nullable()->after($column_before);
                        }
                    }
                    $column_before = $db_field_prop;
                }
            }
            $parsed_json_lomes = json_encode($schemaOutput, JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
            file_put_contents(storage_path('lomes') . '/lomesSchema.json', $parsed_json_lomes);
            // $table->timestamps();
            // $table->foreign('dam_resource_id', 'reource_lomes_fk')
            //     ->constrained('dam_resources')
            //     ->references('id')
            //     ->on('dam_resources')
            //     ->onUpdate('cascade')
            //     ->onDelete('cascade');
        });
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

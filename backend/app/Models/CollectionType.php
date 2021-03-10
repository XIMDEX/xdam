<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'solr_schema', 'json_validator'];

    public function getValidator()
    {
        return json_decode(file_get_contents(storage_path($this->json_validator)));
    }

    public function getSolrSchema()
    {
        return json_decode(file_get_contents(storage_path($this->solr_schema)));
    }

}

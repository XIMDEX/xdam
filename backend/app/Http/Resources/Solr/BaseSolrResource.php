<?php

namespace App\Http\Resources\Solr;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseSolrResource extends JsonResource
{
    public static function generateQuery($searchTerm)
    {
        return "name:$searchTerm^10 name:*$searchTerm*^7 OR data:*$searchTerm*^5";
    }
}
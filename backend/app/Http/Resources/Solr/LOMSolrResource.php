<?php

namespace App\Http\Resources\Solr;

use App\Enums\ResourceType;
use Illuminate\Http\Resources\Json\JsonResource;

class LOMSolrResource extends JsonResource
{
    /**
     * Transform the LOM into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'dam_resource_id'   => $this->dam_resource_id,
            'lang'              => 'en'
        ];
    }
}
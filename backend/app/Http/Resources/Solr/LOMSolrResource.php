<?php

namespace App\Http\Resources\Solr;

use App\Enums\ResourceType;
use Illuminate\Http\Resources\Json\JsonResource;

class LOMSolrResource extends JsonResource
{
    private array $resourceAttributes;
    private string $attributeKey;

    public function __construct($resource, $resourceAttributes, $attributeKey)
    {
        parent::__construct($resource);
        $this->resourceAttributes = $resourceAttributes;
        $this->attributeKey = $attributeKey;
    }
    /**
     * Transform the LOM into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (!array_key_exists($this->attributeKey, $this->resourceAttributes)) {
            return [];
        }

        return [
            'id'                => $this->id,
            'dam_resource_id'   => $this->dam_resource_id,
            'lang'              => 'en',
            'lom_key'           => $this->attributeKey,
            'lom_value'         => $this->resourceAttributes[$this->attributeKey]
        ];
    }
}
<?php

namespace App\Http\Resources\Solr;

// use App\Enums\MediaType;
// use App\Http\Resources\MediaResource;
// use App\Models\Media;
// use App\Utils\DamUrlUtil;
use App\Enums\ResourceType;
use App\Utils\Utils as AppUtils;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentSolrResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        $workspaces = AppUtils::workspacesToName($this->resource->workspaces->pluck('id')->toArray());

        if (property_exists($this->data->description, 'entities_linked')) {

        }
        $entities_linked = property_exists($this->data->description, 'entities_linked')
            ? array_column($this->data->description->entities_linked, 'name')
            : [];

        $entities_non_linked = property_exists($this->data->description, 'entities_non_linked')
            ? array_column($this->data->description->entities_non_linked, 'name')
            : [];

        return [
            'id' => $this->id,
            'external_id' => $this->data->description->id,
            'external_uuid' => $this->data->description->uuid,
            'langcode' => $this->data->description->language,
            'category' => $this->data->description->category,
            'title' => $this->data->description->title,
            'body' => $this->data->description->body,
            'entities_non_linked' =>  count($entities_non_linked) > 0 ? $entities_non_linked : [],
            'entities_linked' => count($entities_linked) > 0 ? $entities_linked : [],
            'active' => $this->active,
            'type' => ResourceType::document,
            'collection' => $this->collection->id,
            'workspaces' => $workspaces,
            'organization' => $this->organization()->id,
            'enhanced' => property_exists($this->data->description, 'enhanced') ? $this->data->description->enhanced : false,
            'enhanced_interactive' => property_exists($this->data->description, 'enhanced_interactive') ? $this->data->description->enhanced_interactive : false,
            'data' => is_object($this->data) ? json_encode($this->data) : $this->data
        ];
    }
}

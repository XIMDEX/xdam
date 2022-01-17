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

        $entities_linked = array_column($this->data->description->entities_linked, 'name');
        $entities_non_linked =  array_column($this->data->description->entities_non_linked, 'name');

        return [
            'id' => $this->id,
            'external_id' => $this->data->description->id,
            'external_uuid' => $this->data->description->uuid,
            'langcode' => $this->data->description->langcode,
            'title' => $this->data->description->title,
            'body' => $this->data->description->body,
            'active' => $this->active,
            'type' => ResourceType::document,
            'entities_non_linked' =>  count($entities_non_linked) > 0 ? $entities_non_linked : [],
            'entities_linked' => count($entities_linked) > 0 ? $entities_linked : [],
            'category' => $this->data->description->category,
            'collection' => $this->collection->id,
            'workspaces' => $workspaces,
            'organization' => $this->organization()->id
        ];
    }
}

<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\MediaResource;
use App\Utils\Utils;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivitySolrResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $files = array_column(
            json_decode(MediaResource::collection($this->getMedia(MediaType::File()->key))->toJson(), true),
            'dam_url'
        );
        $previews = array_column(
            json_decode(MediaResource::collection($this->getMedia(MediaType::Preview()->key))->toJson(), true),
            'dam_url'
        );

        $workspaces = Utils::workspacesToName($this->resource->workspaces->pluck('id')->toArray());

        return [
            'id' => $this->id,
            'name' => $this->data->description->name,
            'data' => is_object($this->data) ? json_encode($this->data) : $this->data,
            'active' => $this->active,
            'type' => ResourceType::activity,
            'tags' => $this->tags()->pluck('name')->toArray() ?? [''],
            'categories' => $this->categories()->pluck('name')->toArray() ?? [''],
            'files' => $files,
            'previews' => $previews,
            'collection' => $this->collection->id,
            'workspaces' => $workspaces,
            'organization' => $this->organization()->id
        ];
    }
}

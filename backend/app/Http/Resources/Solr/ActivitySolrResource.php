<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\MediaResource;
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
        $workspaces = $this->resource->workspaces->pluck('id')->toArray();

        $data = is_object($this->data) ? json_encode($this->data) : $this->data;

        if (property_exists($data, 'description')) {
            $name = property_exists($data->description, 'title') ? $data->description->title : '';
        }

        return [
            'id' => $this->id,
            'name' => $name ?? "",
            'data' => is_object($this->data) ? json_encode($this->data) : $this->data,
            'active' => $this->active,
            'type' => ResourceType::fromValue($this->type)->key,
            'tags' => $this->tags()->pluck('name')->toArray() ?? [''],
            'categories' => $this->categories()->pluck('name')->toArray() ?? [''],
            'files' => $files,
            'previews' => $previews,
            'collection' => $this->collection->id,
            'workspace' => $workspaces,
        ];
    }
}

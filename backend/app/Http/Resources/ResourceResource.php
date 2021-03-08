<?php

namespace App\Http\Resources;


use App\Enums\MediaType;
use App\Enums\ResourceType;

class ResourceResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'active' => $this->active,
            'type' => ResourceType::fromValue($this->type)->key,
            'tags' => $this->tags,
            'categories' => CategoryResource::collection($this->categories),
            'data' => is_object($this->data) ? $this->data : json_decode($this->data),
            'files' => MediaResource::collection($this->getMedia(MediaType::File()->key)),
            'previews' => MediaResource::collection($this->getMedia(MediaType::Preview()->key)),
            'uses' => DamResourceUseResource::collection($this->uses),
            'collection' => $this->resource->collection()->get(),
            'workspace' => $this->resource->workspaces()->get(),
        ];
    }
}

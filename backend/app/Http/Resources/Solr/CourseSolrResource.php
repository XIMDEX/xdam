<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseSolrResource extends JsonResource
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

        if (property_exists($data,  "description") && property_exists($data->description, 'course_source'))
        {
            $active = $data->description->active == true;
            $internal = $data->description->course_source == 'internal';
            $aggregated = $data->description->course_source == 'aggregated';
            $external = $data->description->course_source == 'external';
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'data' => $data,
            'active' => $active ?? $this->active,
            'aggregated' => $aggregated ?? false,
            'internal' => $internal ?? false,
            'external' => $external ?? false,
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

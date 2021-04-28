<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\MediaResource;
use App\Models\Workspace;
use App\Utils\Utils;
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
        $workspaces = Utils::workspacesToName($this->resource->workspaces->pluck('id')->toArray());
        $data = $this->data;

        if (property_exists($data,  "description") && property_exists($data->description, 'course_source'))
        {
            $active = $data->description->active == true;
            $internal = (strpos($data->description->course_source, "internal") !== false);
            $aggregated = (strpos($data->description->course_source, "aggregated") !== false);
            $external = (strpos($data->description->course_source, "external") !== false);
        }

        $tags = $this->tags()->pluck('name')->toArray();
        $categories = $this->categories()->pluck('name')->toArray();

        $finalData = '';

        if (is_object($data)) {
            $data->description->id = $this->id;
            $data->id = $this->id;
            $finalData = $data;
        } else {
            $d = json_decode($data);
            $d->description->id = $this->id;
            $d->id = $this->id;
            $finalData = $d;
        }

        $finalData = is_object($finalData) ? json_encode($finalData) : $finalData;
        return [
            'id' => $this->id,
            'name' => $data->description->name ?? $this->name,
            'data' => $finalData,
            'active' => $active ?? $this->active,
            'aggregated' => $aggregated ?? false,
            'internal' => $internal ?? false,
            'external' => $external ?? false,
            // 'type' => ResourceType::course,
            'tags' => count($tags) > 0 ? $tags : ['untagged'],
            'categories' => count($categories) > 0 ? $categories : ['uncategorized'],
            'files' => $files,
            'previews' => $previews,
            'workspaces' => $workspaces,
            'organization' => $this->organization()->id
        ];
    }
}

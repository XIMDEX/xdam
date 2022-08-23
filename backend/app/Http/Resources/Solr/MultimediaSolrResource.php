<?php

namespace App\Http\Resources\Solr;

use App\Http\Resources\Solr\BaseSolrResource;
use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Utils\DamUrlUtil;
use App\Utils\Utils;

class MultimediaSolrResource extends BaseSolrResource
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

        // If the resource does not have a preview, but has an associated file, take the first one as preview
        if (empty($previews) && !empty($files))
        {
            $previews[] = $files[0];
        }

        $tags = $this->tags()->pluck('name')->toArray();
        $categories = $this->categories()->pluck('name')->toArray();
        $types = [];

        foreach ($files as $dam_url) {
            $mediaId = DamUrlUtil::decodeUrl($dam_url);
            $media = Media::findOrFail($mediaId);
            $mimeType = $media->mime_type;
            $fileType = explode('/', $mimeType)[0];
            if (!in_array($fileType, $types))
            {
                $types[] = $fileType;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->data->description->name,
            'data' => is_object($this->data) ? json_encode($this->data) : $this->data,
            'active' => $this->active,
            'type' => (is_array($files) && count($files) === 0 ? 'image' : $this->type),
            'types' => $types,
            'tags' => count($tags) > 0 ? $tags : ['untagged'],
            'categories' => count($categories) > 0 ? $categories : ['uncategorized'],
            'files' => $files,
            'previews' => $previews,
            'workspaces' => $workspaces,
            'organization' => $this->organization()->id
        ];
    }
}

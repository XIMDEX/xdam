<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Http\Resources\MediaResource;
use App\Http\Resources\Solr\BaseSolrResource;
use App\Models\Media;
use App\Utils\DamUrlUtil;

class MultimediaSolrResource extends BaseSolrResource
{
    protected function getPreviews()
    {
        $files = $this->getFiles();
        $previews = array_column(
            json_decode(MediaResource::collection($this->getMedia(MediaType::Preview()->key))->toJson(), true),
            'dam_url'
        );

        // If the resource does not have a preview, but has an associated file, take the first one as preview
        if (empty($previews) && !empty($files))
        {
            $previews[] = $files[0];
        }

        return $previews;
    }

    protected function getType()
    {
        $files = $this->getFiles();
        return (is_array($files) && count($files) === 0 ? 'image' : $this->type);
    }

    private function getTypes($files)
    {
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

        return $types;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $files = $this->getFiles();

        return [
            'id' => $this->getID(),
            'name' => $this->getName(),
            'data' => $this->getData(),
            'active' => $this->getActive(),
            'type' => $this->getType(),
            'types' => $this->getTypes($files),
            'tags' => $this->formatTags($this->getTags()),
            'categories' => $this->formatCategories($this->getCategories()),
            'files' => $files,
            'previews' => $this->getPreviews(),
            'workspaces' => $this->getWorkspaces(),
            'organization' => $this->getOrganization()
        ];
    }
}

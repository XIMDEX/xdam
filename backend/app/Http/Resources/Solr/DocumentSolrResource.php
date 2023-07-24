<?php

namespace App\Http\Resources\Solr;

use App\Http\Resources\Solr\BaseSolrResource;
use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\MediaConversion;
use App\Utils\Utils as AppUtils;
use App\Utils\DamUrlUtil;

class DocumentSolrResource extends BaseSolrResource
{
    public function __construct($resource, $lomSolrClient = null, $lomesSolrClient = null, $toSolr = false)
    {
        parent::__construct($resource, $lomSolrClient, $lomesSolrClient);
    }

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

    private function getConversions()
    {
        $conversions = [];
        $asMedia = Media::where('model_id', $this->id)->get();

        foreach ($asMedia as $item) {
            $mediaConversions = $item->conversions()->pluck('file_compression');

            foreach ($mediaConversions as $subitem) {
                $conversions[] = $subitem;
            }
        }

        // $conversions = $this->conversions()->pluck('file_compression')->toArray();
        return $conversions;
    }

    protected function getCoreResourceType()
    {
        return ResourceType::document;
    }

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

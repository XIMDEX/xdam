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
            $previews[] = config('ximdex.DOCUMENT_PLACEHOLDER_IMAGE');
        }

        return $previews;
    }

    protected function getType()
    {
        $files = $this->getFiles();
        return (is_array($files) && count($files) === 0 ? 'document' : $this->type);
    }

    private function getTypes($files)
    {
        $types = [];

        foreach ($files as $dam_url) {
            $mediaId = DamUrlUtil::decodeUrl($dam_url);
            $media = Media::findOrFail($mediaId);
            $mimeType = $media->mime_type;
            $fileType = explode('/', $mimeType)[1];
            if (!in_array($fileType, $types))
            {
                $types[] = $fileType;
            }
        }

        return $types;
    }


    protected function formatSemanticTags($tags)
    {
        $toSolr = [];
        foreach ($tags as $tag) {
            try {
                $toSolr[] = $tag->name;
            } catch (\Throwable $th) {}
        }
        return $toSolr;
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

    protected function getSemanticTags()
    {
        $semantic_tags = $this->data->description->semantic_tags ?? [];
        return $semantic_tags;
    }


    protected function getData($tags = null, $categories = null, $semanticTags = null)
    {
        $data = $this->data;
        $data = (!is_object($data) ? json_decode($data) : $data);
        $data->id = $this->id;
        $data->description->id = $this->id;
        $data->description->name = $this->name;
        $data->description->semantic_tags = $this->formatSemanticTags($this->getSemanticTags());
        $data->description->tags = $tags;
        $data->description->categories = $categories;
        $data->lom = $this->getLOMRawValues('lom');
        $data->lomes = $this->getLOMRawValues('lomes');
        $finalData = $data;
        $finalData = is_object($finalData) ? json_encode($finalData) : $finalData;
        return $finalData;
    }

    protected function getCoreResourceType()
    {
        return ResourceType::document;
    }

    private function getCanDownload()
    {
        return $this->data->can_download ?? false;
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
        $tags = $this->getTags();
        $semanticTags = $this->getSemanticTags();
        $categories = $this->getCategories();
        $output = [
            'id'                    => $this->getID(),
            'name'                  => $this->getName(),
            'data'                  => $this->getData($tags, $categories, $semanticTags),
            'active'                => $this->getActive(),
            'can_download'          => $this->getCanDownload(),
            'type'                  => $this->getType(),
            'types'                 => $this->getTypes($files),
            'tags'                  => $this->formatTags($this->getTags()),
            'categories'            => $this->formatCategories($this->getCategories()),
            'semantic_tags'         => $this->formatSemanticTags($semanticTags),
            'files'                 => $files,
            'conversions'           => $this->getConversions(),
            'previews'              => $this->getPreviews(),
            'workspaces'            => $this->getWorkspaces(),
            'organization'          => $this->getOrganization(),
            'collections'           => $this->getCollections(),
            'core_resource_type'    => $this->getCoreResourceType(),
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'lom'                   => $this->getLOMValues(),
            'lomes'                 => $this->getLOMValues('lomes'),
            'lang'                  => $this->data->description->lang
        ];
        return $output;
    }
}

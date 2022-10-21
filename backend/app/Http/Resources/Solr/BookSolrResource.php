<?php

namespace App\Http\Resources\Solr;

use App\Http\Resources\Solr\BaseSolrResource;
use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\MediaResource;
use App\Utils\Utils as AppUtils;

class BookSolrResource extends BaseSolrResource
{
    public function __construct($resource, $reindexLOM = false,
                                $lomSolrClient = null)
    {
        parent::__construct($resource, $reindexLOM, $lomSolrClient);
    }

    protected function formatCategories($categories)
    {
        return $categories ?? ['uncategorized'];
    }

    protected function getType()
    {
        return ResourceType::book;
    }

    protected function getCoreResourceType()
    {
        return ResourceType::book;
    }

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

        return [
            'id'                    => $this->getID(),
            'name'                  => $this->getName(),
            'data'                  => $this->getData(),
            'active'                => $this->getActive(),
            'type'                  => $this->getType(),
            'tags'                  => $this->formatTags($this->getTags()),
            'categories'            => $this->formatCategories($this->getCategories()),
            'files'                 => $this->getFiles(),
            'previews'              => $this->getPreviews(),
            'collection'            => $this->collection->id,
            'workspaces'            => $this->getWorkspaces(),
            'organization'          => $this->getOrganization(),
            'units'                 => $this->data->description->units ?? 0,
            'isbn'                  => $this->data->description->isbn ?? '',
            'lang'                  => $this->data->description->lang ?? getenv('BOOK_DEFAULT_LANGUAGE'),
            'collections'           => $this->getCollections(),
            'core_resource_type'    => $this->getCoreResourceType(),
            'lom'                   => $this->getLOMs()
        ];
    }
}

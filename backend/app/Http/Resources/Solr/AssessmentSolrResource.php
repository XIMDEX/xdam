<?php

namespace App\Http\Resources\Solr;

use App\Enums\ResourceType;
use App\Http\Resources\Solr\BaseSolrResource;

class AssessmentSolrResource extends BaseSolrResource
{
    public function __construct($resource, $lomSolrClient = null, $lomesSolrClient = null, $toSolr = false)
    {
        parent::__construct($resource, $lomSolrClient, $lomesSolrClient);
    }

    protected function formatTags($tags)
    {
        return $tags ?? [''];
    }

    protected function formatCategories($categories)
    {
        return $categories ?? [''];
    }

    protected function getType()
    {
        return ResourceType::assessment;
    }

    protected function getCoreResourceType()
    {
        return ResourceType::assessment;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
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
            'collections'           => $this->getCollections(),
            'core_resource_type'    => $this->getCoreResourceType(),
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'lom'                   => $this->getLOMValues(),
            'lomes'                 => $this->getLOMValues('lomes'),
            'unit'                  => $this->data->description->unit ?? $this->data->description->units ?? 0,
            'isbn'                  => $this->data->description->isbn ?? $this->data->description->isbns ?? '',
            'language_default'      => $this->data->description->language_default ?? getenv('BOOK_DEFAULT_LANGUAGE'),
            'available_languages'   => $this->data->description->available_languages ?? [getenv('BOOK_DEFAULT_LANGUAGE')],
            'activities'            => $this->data->description->activities ?? []
        ];
    }
}

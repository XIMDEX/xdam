<?php

namespace App\Http\Resources\Solr;

use App\Enums\ResourceType;
use App\Http\Resources\Solr\BaseSolrResource;

class AssessmentSolrResource extends BaseSolrResource
{
    public function __construct($resource, $reindexLOM = false)
    {
        parent::__construct($resource, $reindexLOM);
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
            'core_resource_type'    => $this->getCoreResourceType()
        ];
    }
}

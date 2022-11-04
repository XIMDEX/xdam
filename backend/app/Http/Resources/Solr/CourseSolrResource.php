<?php

namespace App\Http\Resources\Solr;

use App\Enums\ResourceType;
use App\Http\Resources\Solr\BaseSolrResource;

class CourseSolrResource extends BaseSolrResource
{
    public function __construct($resource, $lomSolrClient = null, $lomesSolrClient = null)
    {
        parent::__construct($resource, $lomSolrClient, $lomesSolrClient);
    }

    public static function generateQuery($searchTerm, $searchPhrase)
    {
        $query = "name:$searchTerm^10 name:*$searchTerm*^7 OR data:*$searchTerm*^5 ";
        $query .= "lom:*$searchTerm*^4 OR lomes:*$searchTerm*^4 achievements:*$searchTerm*^3 OR preparations:*$searchTerm*^3";
        return $query;
    }
    
    protected function getData($tags = null, $categories = null)
    {
        $data = $this->data;

        if (!is_object($data))
            $data = json_decode($data);

        $data->id = $this->id;
        $data->description->id = $this->id;
        $data->description->name = $this->name;
        $data->description->tags = $tags;
        $data->description->categories = $categories;
        $finalData = $data;
        $finalData = is_object($finalData) ? json_encode($finalData) : $finalData;

        return $finalData;
    }

    protected function getName()
    {
        return $this->data->description->name ??
            (property_exists($this->data->description, 'course_title') ? $this->data->description->course_title : $this->name);
    }

    protected function getActive()
    {
        if (property_exists($this->data, 'description') && property_exists($this->data->description, 'course_source'))
            $active = $this->data->description->active == true;
        
        return $active ?? $this->active;
    }

    protected function getType()
    {
        return ResourceType::course;
    }

    private function getBooleanDataValue($key)
    {
        if (property_exists($this->data, 'description') && property_exists($this->data->description, 'course_source'))
            $value = (strpos($this->data->description->course_source, $key) !== false);

        return $value ?? false;
    }

    protected function getCoreResourceType()
    {
        return ResourceType::course;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $tags = $this->getTags();
        $categories = $this->getCategories();
        $cost = $this->data->description->cost ?? 0;

        return [
            'id'                    => $this->getID(),
            // Way to get name, temporal required by frontend. Must be only $data->description->name
            'name'                  => $this->getName(),
            'data'                  => $this->getData($tags, $categories),
            'active'                => $this->getActive(),
            'aggregated'            => $this->getBooleanDataValue('aggregated'),
            'internal'              => $this->getBooleanDataValue('internal'),
            'external'              => $this->getBooleanDataValue('external'),
            'type'                  => $this->getType(),
            'tags'                  => $this->formatTags($tags),
            'categories'            => $this->formatCategories($categories),
            'files'                 => $this->getFiles(),
            'previews'              => $this->getPreviews(),
            'workspaces'            => $this->getWorkspaces(),
            'organization'          => $this->getOrganization(),
            // Cost is an integer representing the value. int 1000 = 10.00€
            'cost'                  => $cost,
            'currency'              => $this->data->description->currency ?? 'EUR',
            'isFree'                => !($cost > 0),
            // Duration is an integer representing the value. int 1000 = 1000 seconds
            'duration'              => $this->data->description->duration ?? 0,
            'skills'                => $this->data->description->skills ?? [],
            'preparations'          => $this->data->description->preparations ?? [],
            'achievements'          => $this->data->description->achievements ?? [],
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'collections'           => $this->getCollections(),
            'core_resource_type'    => $this->getCoreResourceType(),
            'lom'                   => $this->getLOMValues(),
            'lomes'                 => $this->getLOMESValues()
        ];
    }
}

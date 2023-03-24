<?php

namespace App\Http\Resources\Solr;

use App\Enums\ResourceType;
use App\Http\Resources\Solr\BaseSolrResource;
use App\Utils\Texts;

class CourseSolrResource extends BaseSolrResource
{

    public function __construct($resource, $lomSolrClient = null, $lomesSolrClient = null)
    {
        parent::__construct($resource, $lomSolrClient, $lomesSolrClient);
    }

    public static function generateQuery($searchTerm, $searchPhrase)
    {
        $query = parent::generateQuery($searchTerm, $searchPhrase);
        $query .= " achievements:*$searchTerm*^3 OR preparations:*$searchTerm*^3";
        return $query;
    }

    protected function getData($tags = null, $categories = null)
    {
        $data = $this->data;
        $data = (!is_object($data) ? json_decode($data) : $data);
        $data->id = $this->id;
        $data->description->id = $this->id;
        $data->description->name = $this->name;
        $data->description->tags = $tags;
        $data->description->categories = $categories;
        // $data->lom = $this->getLOMRawValues('lom');
        // $data->lomes = $this->getLOMRawValues('lomes');
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
            Texts::web('name')                  => $this->getName(),
            Texts::web('data')                  => $this->getData($tags, $categories),
            Texts::web('active')                => $this->getActive(),
            Texts::web('aggregated')            => $this->getBooleanDataValue('aggregated'),
            Texts::web('internal')              => $this->getBooleanDataValue('internal'),
            Texts::web('external')              => $this->getBooleanDataValue('external'),
            Texts::web('type')                  => $this->getType(),
            Texts::web('tags')                  => $this->formatTags($tags),
            Texts::web('categories')            => $this->formatCategories($categories),
            Texts::web('files')                 => $this->getFiles(),
            Texts::web('previews')              => $this->getPreviews(),
            Texts::web('workspaces')            => $this->getWorkspaces(),
            Texts::web('organization')          => $this->getOrganization(),
            // Cost is an integer representing the value. int 1000 = 10.00€
            Texts::web('cost')                  => $cost,
            Texts::web('currency')              => $this->data->description->currency ?? 'EUR',
            Texts::web('isFree')                => !($cost > 0),
            // Duration is an integer representing the value. int 1000 = 1000 seconds
            Texts::web('duration')              => $this->data->description->duration ?? 0,
            Texts::web('skills')                => $this->data->description->skills ?? [],
            Texts::web('preparations')          => $this->data->description->preparations ?? [],
            Texts::web('achievements')          => $this->data->description->achievements ?? [],
            Texts::web('created_at')            => $this->created_at,
            Texts::web('updated_at')            => $this->updated_at,
            Texts::web('deleted_at')            => $this->deleted_at,
            Texts::web('is_deleted')            => $this->deleted_at == null ? false : true,
            Texts::web('collections')           => $this->getCollections(),
            Texts::web('core_resource_type')    => $this->getCoreResourceType(),
            // 'lom'                   => $this->getLOMValues(),
            // 'lomes'                 => $this->getLOMValues('lomes')
        ];
    }
}

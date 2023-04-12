<?php

namespace App\Http\Resources\Solr;

use App\Enums\ResourceType;
use App\Http\Resources\Solr\BaseSolrResource;
use App\Http\Resources\Solr\Traits\HasSemanticTags;
use App\Utils\Texts;

class CourseSolrResource extends BaseSolrResource
{

    use HasSemanticTags;

    private $toSolr;

    public function __construct($resource, $lomSolrClient = null, $lomesSolrClient = null, $toSolr = false)
    {
        parent::__construct($resource, $lomSolrClient, $lomesSolrClient);
        $this->toSolr = $toSolr;
    }

    public static function generateQuery($searchTerm, $searchPhrase)
    {
        $query = parent::generateQuery($searchTerm, $searchPhrase);
        if ($searchTerm !== '') {
            $query .= " achievements:*$searchTerm*^3 OR preparations:*$searchTerm*^3";
        }
        return $query;
    }

    protected function getData($tags = null, $categories = null, $semanticTags = null)
    {
        $data = $this->data;
        $data = (!is_object($data) ? json_decode($data) : $data);
        $data->id = $this->id;
        $data->description->id = $this->id;
        $data->description->name = $this->name;
        $data->description->semantic_tags = $this->getSemanticTags();
        $data->description->tags = $tags;
        $data->description->categories = $categories;
        $data->lom = $this->getLOMRawValues('lom');
        $data->lomes = $this->getLOMRawValues('lomes');
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
        if ($this->toSolr) {
            return $this->toArraySolr($request);
        }

        $tags = $this->getTags();
        $categories = $this->getCategories();
        $cost = $this->data->description->cost ?? 0;

        return [
            'id'                    => $this->getID(),
            // Way to get name, temporal required by frontend. Must be only $data->description->name
            Texts::web('name')                  => $this->getName(),
            Texts::web('data')                  => $this->getData($tags, $categories, $semanticTags),
            Texts::web('active')                => $this->getActive(),
            Texts::web('aggregated')            => $this->getBooleanDataValue('aggregated'),
            Texts::web('internal')              => $this->getBooleanDataValue('internal'),
            Texts::web('external')              => $this->getBooleanDataValue('external'),
            Texts::web('type')                  => $this->getType(),
            Texts::web('tags')                  => $this->formatTags($tags),
            Texts::web('semantic_tags')         => $this->getFormattedSemanticTags(),
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
            Texts::web('language')              => $this->data->description->language ?? 'en-EN',
            Texts::web('corporations')          => $this->data->description->corporations ?? 'unassigned',
            Texts::web('collections')           => $this->getCollections(),
            Texts::web('core_resource_type')    => $this->getCoreResourceType(),
            // 'lom'                   => $this->getLOMValues(),
            // 'lomes'                 => $this->getLOMValues('lomes')
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function toArraySolr($request)
    {
        $tags = $this->getTags();
        $categories = $this->getCategories();
        $semanticTags = $this->getSemanticTags();
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
            'semantic_tags'         => $this->formatSemanticTags($semanticTags),
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
            'deleted_at'            => $this->deleted_at,
            'is_deleted'            => $this->deleted_at == null ? false : true,
            'language'              => $this->data->description->language ?? 'en-EN',
            'corporations'          => $this->data->description->corporations ?? 'Unassigned',
            'collections'           => $this->getCollections(),
            'core_resource_type'    => $this->getCoreResourceType(),
            // 'lom'                   => $this->getLOMValues(),
            // 'lomes'                 => $this->getLOMValues('lomes')
        ];
    }
}

<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Http\Resources\MediaResource;
use App\Http\Resources\Solr\LOMSolrResource;
use App\Models\Lom;
use App\Models\Lomes;
use App\Utils\Utils;
use Illuminate\Http\Resources\Json\JsonResource;
use Solarium\Client;

class BaseSolrResource extends JsonResource
{
    private $lomSolrClient;
    private $lomesSolrClient;

    public function __construct($resource, $lomSolrClient = null, $lomesSolrClient = null)
    {
        parent::__construct($resource);
        $this->lomSolrClient = $lomSolrClient;
        $this->lomesSolrClient = $lomesSolrClient;
    }

    public static function generateQuery($searchTerm, $searchPhrase)
    {
        $query = "name:$searchTerm^10 name:*$searchTerm*^7 OR data:*$searchTerm*^5 ";
        $query .= "lom:*$searchTerm*^4 OR lomes:*$searchTerm*^3";
        return $query;
    }
    
    protected function getFiles()
    {
        return array_column(
            json_decode(MediaResource::collection($this->getMedia(MediaType::File()->key))->toJson(), true),
            'dam_url'
        );
    }

    protected function getPreviews()
    {
        return array_column(
            json_decode(MediaResource::collection($this->getMedia(MediaType::Preview()->key))->toJson(), true),
            'dam_url'
        );
    }

    protected function getData($tags = null, $categories = null)
    {
        return is_object($this->data) ? json_encode($this->data) : $this->data;
    }
    
    protected function getWorkspaces()
    {
        // return Utils::workspacesToName($this->resource->workspaces->pluck('id')->toArray());
        // return Utils::formatWorkspaces($this->resource->workspaces->pluck('id')->toArray());
        return $this->resource->workspaces->pluck('id')->toArray();
    }

    protected function getTags()
    {
        return $this->tags()->pluck('name')->toArray();
    }

    protected function formatTags($tags)
    {
        return count($tags) > 0 ? $tags : ['untagged'];
    }

    protected function getCategories()
    {
        return $this->categories()->pluck('name')->toArray();
    }

    protected function formatCategories($categories)
    {
        return count($categories) > 0 ? $categories : ['uncategorized'];
    }

    protected function getID()
    {
        return $this->id;
    }

    protected function getName()
    {
        return $this->data->description->name;
    }

    protected function getOrganization()
    {
        return $this->organization()->id;
    }

    protected function getActive()
    {
        return $this->active;
    }

    protected function getType()
    {
        return '';
    }

    protected function getCoreResourceType()
    {
        return '';
    }

    protected function getCollections()
    {
        return [$this->collection->id];
    }

    protected function getMaxFiles()
    {
        return $this->collection->getMaxNumberOfFiles();
    }

    private function formatLOMValues($element)
    {
        $values = [];
        $keySeparator = '---';
        $valueSeparator = '===';

        if ($element !== null) {
            $auxValues = $element->getResourceLOMValues();

            foreach ($auxValues as $item) {
                $key = $item['key'];
                $subkey = $item['subkey'];
                $value = $item['value'];
                $auxItem = $key;
                $auxItem .= ($subkey !== null ? ($keySeparator . $subkey) : '');
                $auxItem .= ($valueSeparator . $value);
                $values[] = $auxItem;
            }
        }

        return $values;
    }

    protected function getLOMValues()
    {
        $lom = Lom::where('dam_resource_id', $this->id)->first();
        return $this->formatLOMValues($lom);
    }

    protected function getLOMESValues()
    {
        $lomes = Lomes::where('dam_resource_id', $this->id)->first();
        return $this->formatLOMValues($lomes);
    }
}
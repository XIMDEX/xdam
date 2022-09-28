<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Http\Resources\MediaResource;
use App\Utils\Utils;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseSolrResource extends JsonResource
{
    public static function generateQuery($searchTerm)
    {
        return "name:$searchTerm^10 name:*$searchTerm*^7 OR data:*$searchTerm*^5";
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
}
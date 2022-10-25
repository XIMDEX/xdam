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
    private bool $reindexLOM;
    private $lomSolrClient;
    private $lomesSolrClient;

    public function __construct($resource, $reindexLOM = false,
                                $lomSolrClient = null,
                                $lomesSolrClient = null)
    {
        parent::__construct($resource);
        $this->reindexLOM = $reindexLOM;
        $this->lomSolrClient = $lomSolrClient;
        $this->lomesSolrClient = $lomesSolrClient;
    }

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

    protected function getCollections()
    {
        return [$this->collection->id];
    }

    protected function getMaxFiles()
    {
        return $this->collection->getMaxNumberOfFiles();
    }

    private function reindexLOM($lomItem, $client)
    {
        $createCommand = $client->createUpdate();
        $lomDoc = json_decode((new LOMSolrResource($lomItem))->toJson(), true);
        $document = $createCommand->createDocument();

        foreach ($lomDoc as $lomKey => $lomValue) {
            $document->$lomKey = $lomValue;
        }

        $createCommand->addDocument($document);
        $createCommand->addCommit();
        $result = $client->update($createCommand);
    }

    protected function reindexLOMs()
    {
        if ($this->reindexLOM && $this->lomSolrClient !== null
                && $this->lomesSolrClient !== null) {
            $lom = Lom::where('dam_resource_id', $this->id)->first();
            if ($lom !== null) $this->reindexLOM($lom, $this->lomSolrClient);
    
            $lomes = Lomes::where('dam_resource_id', $this->id)->first();
            if ($lomes !== null) $this->reindexLOM($lomes, $this->lomesSolrClient);
        }
    }

    private function getLOM($client)
    {
        $documentFound = '';

        try {
            $query = $client->createSelect();
            $query->setQuery('dam_resource_id:' . $this->id);
            $result = $client->execute($query);

            foreach ($result as $item) {
                $fields = $item->getFields();
                $documentFound = json_encode($fields);
            }
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            $documentFound = '';
        }

        return $documentFound;
    }

    protected function getLOMs($lomType = 'lom')
    {
        $client = $this->lomSolrClient;
        $client = ($lomType == 'lomes' ? $this->lomesSolrClient : $client);
        return $this->getLOM($client);
    }
}
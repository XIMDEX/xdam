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
    /**
     * @var array $mediaResources
     */
    private array $mediaResources;

    /**
     * @var Client $lomSolrClient
     */
    private $lomSolrClient;

    /**
     * @var Client $lomesSolrClient
     */
    private $lomesSolrClient;

    /**
     * @var string $specialCharacter
     */
    private string $specialCharacter;

    /**
     * @var string $keySeparator
     */
    private string $keySeparator;

    /**
     * @var string $valueSeparator
     */
    private string $valueSeparator;

    /**
     * @var array $charactersMap
     */
    private array $charactersMap;

    public function __construct($resource, array $mediaResources, $lomSolrClient = null, $lomesSolrClient = null)
    {
        parent::__construct($resource);

        $this->mediaResources = $mediaResources;
        $this->lomSolrClient = $lomSolrClient;
        $this->lomesSolrClient = $lomesSolrClient;
        $this->manageSolrFacetsConfig();
    }

    private function manageSolrFacetsConfig()
    {
        $config = config('solr_facets', []);
        $this->specialCharacter = '';
        $this->keySeparator = '';
        $this->valueSeparator = '';
        $this->charactersMap = [];

        if (array_key_exists('constants', $config)) {
            $config = $config['constants'];
            $this->specialCharacter = $config['special_character'];
            $this->keySeparator = Utils::getRepetitiveString($this->specialCharacter, $config['key_separator']);
            $this->valueSeparator = Utils::getRepetitiveString($this->specialCharacter, $config['value_separator']);
            $this->charactersMap = $config['characters_map'];
        }
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
        $data = $this->data;
        $data = (!is_object($data) ? json_decode($data) : $data);
        $data->lom = $this->getLOMRawValues('lom');
        $data->lomes = $this->getLOMRawValues('lomes');
        $finalData = $data;
        $finalData = is_object($finalData) ? json_encode($finalData) : $finalData;
        return $finalData;
    }
    
    protected function getWorkspaces()
    {
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

    protected function getLOMRawValues(string $type, bool $allFields = true, $attempt = 0)
    {
        $element = null;
        $values = null;
        
        try {
            if ($type == 'lom') {
                $element = $this->lom()->first();
            } else if ($type == 'lomes') {
                $element = $this->lomes()->first();
            }
    
            if ($element !== null) {
                $values = $element->getResourceLOMValues($allFields);
            }
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            if ($attempt < 15) {
                sleep(1);
                $values = $this->getLOMRawValues($type, $allFields, $attempt + 1);
            }
        }

        return $values;
    }

    private function formatValueString($value)
    {
        $defValue = $value;

        if ($value !== null) {
            foreach ($this->charactersMap as $characterItem) {
                $replace = Utils::getRepetitiveString($this->specialCharacter, $characterItem['to']);
                $defValue = str_replace($characterItem['from'], $replace, $defValue);
            }
        }

        return $defValue;
    }

    protected function getLOMValues(string $type = 'lom')
    {
        $values = [];
        $rawValues = $this->getLOMRawValues($type, false);

        if ($rawValues !== null) {
            foreach ($rawValues as $item) {
                $key = $item['key'];
                $subkey = $item['subkey'];
                $value = $item['value'];
                $auxItem = $key;
                $auxItem .= ($subkey !== null ? ($this->keySeparator . $subkey) : '');
                $auxItem .= ($this->valueSeparator . $value);
                $auxItem = $this->formatValueString($auxItem);
                $values[] = $auxItem;
            }
        }

        return $values;
    }

    protected function getTikaMetadata()
    {
        $values = [];

        foreach ($this->mediaResources as $item) {
            $rawValues = $item->getFileMetadata(false);
            if ($rawValues !== null) $rawValues = json_decode($rawValues);

            foreach ($rawValues as $rKey => $rValue) {
                if ($rValue !== null) {
                    if (gettype($rValue) === 'array') $rValue = json_encode($rValue);
                    $auxValue = $rKey . $this->valueSeparator . $rValue;
                    $auxValue = $this->formatValueString($auxValue);
                    if (!in_array($auxValue, $values)) $values[] = $auxValue;
                }
            }
        }

        return $values;
    }
}
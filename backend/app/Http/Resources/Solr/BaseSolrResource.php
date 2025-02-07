<?php

namespace App\Http\Resources\Solr;

use App\Enums\MediaType;
use App\Http\Resources\MediaResource;
use App\Http\Resources\Solr\LOMSolrResource;
use App\Models\Lom;
use App\Models\Lomes;
use App\Services\Media\MediaSizeImage;
use App\Utils\DamUrlUtil;
use App\Utils\Utils;
use Illuminate\Http\Resources\Json\JsonResource;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
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
        $query = '';
        if ($searchPhrase !== "") {
            $query .= "name:*$searchPhrase*^10 ";
            $query .= "OR lom:$searchPhrase^4 OR lomes:*$searchPhrase*^3 ";
        }
        if ($searchTerm !== '') {
            if ($query !== '') $query .= 'OR ';
            $query .= "name:$searchTerm^9  OR data:*$searchTerm*^5 ";
            $query .= "OR lom:*$searchTerm*^4 OR lomes:*$searchTerm*^3 ";
        }
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
        $data->description->semantic_tags = $this->formatSemanticTags($this->getSemanticTags());
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
        if (count($tags) < 0) return ['untagged'];
        $output = [];
        foreach ($tags as $tag) {
            if (is_array($tag)) {
                // search on languages
                $output[] = $tag;
            } else {
                $output[] = $tag;
            }
        }
        return $output;
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
        return $this->data->description->name ?? "";
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

    protected function getLOMRawValues(string $type, bool $allFields = true)
    {
        $element = null;
        $values = null;

        if ($type == 'lom') {
            $element = $this->lom()->first();
        } else if ($type == 'lomes') {
            $element = $this->lomes()->first();
        }

        if ($element !== null) {
            $values = $element->getResourceLOMValues($allFields);
        }

        return $values;
    }

    protected function getLOMValues(string $type = 'lom')
    {
        $values = [];
        $solrFacetsConfig = config('solr_facets', []);

        if (array_key_exists('constants', $solrFacetsConfig)) {
            $solrFacetsConfig = $solrFacetsConfig['constants'];
            $rawValues = $this->getLOMRawValues($type, false);
            $specialCharacter = $solrFacetsConfig['special_character'];
            $keySeparator = Utils::getRepetitiveString($specialCharacter, $solrFacetsConfig['key_separator']);
            $valueSeparator = Utils::getRepetitiveString($specialCharacter, $solrFacetsConfig['value_separator']);
            $charactersMap = $solrFacetsConfig['characters_map'];

            if ($rawValues !== null) {
                foreach ($rawValues as $item) {
                    $key = $item['key'];
                    $subkey = $item['subkey'];
                    $value = $item['value'];
                    $auxItem = $key;
                    $auxItem .= ($subkey !== null ? ($keySeparator . $subkey) : '');
                    $auxItem .= ($valueSeparator . $value);

                    foreach ($charactersMap as $characterItem) {
                        $replace = Utils::getRepetitiveString($specialCharacter, $characterItem['to']);
                        $auxItem = str_replace($characterItem['from'], $replace, $auxItem);
                    }

                    $values[] = $auxItem;
                }
            }
        }



        return $values;
    }

    protected function getSemanticTags()
    {
        $semantic_tags = $this->data->description->semantic_tags ?? [];
        return $semantic_tags;
    }

    protected function formatSemanticTags($tags)
    {
        $toSolr = [];
        foreach ($tags as $tag) {
            try {
                $toSolr[] = $tag->name;
            } catch (\Throwable $th) {}
        }
        return $toSolr;
    }

    protected function imgToBase64() {

        $media = $this->getMedia(MediaType::File()->key);
        $file = $media->first();
        $mediaPath = $file->getPath();
        $manager = new ImageManager(new Driver());
        $image   = $manager->read($mediaPath);
        $image->coverDown(256, 144);
        $base64 = (string) $image->toPng()->toDataUri();
        return $base64;
    }
}

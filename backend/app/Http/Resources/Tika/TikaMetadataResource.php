<?php

namespace App\Http\Resources\Tika;

use Illuminate\Http\Resources\Json\JsonResource;

class TikaMetadataResource extends JsonResource
{
    private $element;

    /**
     * @var string $mediaPath
     */
    protected string $mediaPath;

    /**
     * @var boolean $allFields
     */
    private bool $allFields;

    /**
     * @var array $solrFields
     */
    private array $solrFields;

    public function __construct($element, string $mediaPath, bool $allFields = true)
    {
        parent::__construct($element);

        $this->element = $element;
        $this->mediaPath = $mediaPath;
        $this->allFields = $allFields;
        $this->solrFields = $this->getSolrFields();
    }

    private function getSolrFields()
    {
        $config = config('solr_facets', []);
        if (array_key_exists('tika_metadata', $config)) return $config['tika_metadata'];
        return [];
    }

    public function toArray($request)
    {
        $resource = [];

        foreach ($this->element as $key => $value) {
            if ($this->allFields || in_array($key, $this->solrFields)) {
                $resource[$key] = $value;
            }
        }

        return $resource;
    }
}
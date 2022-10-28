<?php

namespace App\Http\Resources\Solr;

use App\Models\DamResource;
use App\Utils\Utils;
use Illuminate\Http\Resources\Json\JsonResource;

use function Lambdish\Phunctional\instance_of;

class LOMSolrResource extends JsonResource
{
    private DamResource $damResource;
    private string $lomKey;
    private $lomValue;

    /**
     * Constructor
     * @param $element
     * @param DamResource $damResource
     * @param string $lomKey
     * @param $lomValue
     */
    public function __construct($element, $damResource, $lomKey, $lomValue)
    {
        parent::__construct($element);
        $this->damResource = $damResource;
        $this->lomKey = $lomKey;
        $this->lomValue = $lomValue;
    }

    /**
     * Gets the LOM language
     * @return string
     */
    private function getLanguage()
    {
        switch (get_class($this->resource)) {
            case 'App\Models\Lom':
                $lang = 'en';
                break;

            case 'App\Models\Lomes':
                $lang = 'es';
                break;

            default:
                $lang = '';
                break;
        }

        return $lang;
    }

    /**
     * Returns the LOM schema
     * @return array
     */
    private function getLomSchema()
    {
        switch (get_class($this->resource)) {
            case 'App\Models\Lom':
                $schema = Utils::getLomSchema(true);
                break;

            case 'App\Models\Lomes':
                $schema = Utils::getLomesSchema(true);
                break;
            
            default:
                $schema = [];
                break;
        }

        return $schema;
    }

    /**
     * Transform the LOM into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'dam_resource_id'   => $this->dam_resource_id,
            'dam_collection_id' => $this->damResource->collection->id,
            'lang'              => $this->getLanguage(),
            'lom_key'           => $this->lomKey,
            'lom_value'         => $this->lomValue
        ];
    }
}
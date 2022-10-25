<?php

namespace App\Http\Resources\Solr;

use App\Utils\Utils;
use Illuminate\Http\Resources\Json\JsonResource;

use function Lambdish\Phunctional\instance_of;

class LOMSolrResource extends JsonResource
{
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

    private function getLomValues()
    {
        $resourceAttributes = $this->resource->getResourceLOMValues();
        $lomAttributes = [];

        foreach ($resourceAttributes as $key => $value) {
            $lomAttributes[$key] = json_decode($value, true);
        }

        return json_encode($lomAttributes);
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
            'lom_id'            => $this->id,
            'dam_resource_id'   => $this->dam_resource_id,
            'lang'              => $this->getLanguage(),
            'lom_value'         => $this->getLomValues()
        ];
    }
}
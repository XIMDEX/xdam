<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    public function hasJsonData($string)
    {
        $array = @json_decode($string, true);
        return !empty($string) && is_string($string) && is_array($array) && !empty($array) && json_last_error() == 0;
    }

    public function transformToJson($string)
    {
        if ($this->hasJsonData($string)) {
            return json_decode($string);
        } else {
            return json_decode(json_encode($string), true);
        }
    }

    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content' => $this->transformToJson($this->content)->description
        ];
    }

    public function appendKeyToData($key, $value)
    {
        if ($this['code'] == 200) {
            $data = $this['data'];
            $data[$key] = $value;
            $this['data'] = $data;
        } else {
            $this['error'] = ['data' => [$this['error']], $key => $value];
        }
    }
}

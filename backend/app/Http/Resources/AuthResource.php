<?php

namespace App\Http\Resources;

class AuthResource extends BaseResource
{

    public function appendKeyToData($key, $value)
    {
        if ($this['code'] == 200) {
            $data = $this['data'];
            $data[$key] = $value;
            $this['data'] = $data;
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'status' => $this['code'] == 200 ? 'Success' : 'Error',
            'error' => $this['code'] != 200 ? ['data' => [$this['error']]] : null,
            'code' => $this['code'],
            'data' => $this['code'] == 200 ? $this['data'] : null
        ];
    }
}

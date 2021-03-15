<?php

namespace App\Http\Resources;

class AuthResource extends BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'error' => $this['code'] != 200 ? ['data' => [$this['error']]] : null,
            'code' => $this['code'],
            'data' => $this['code'] == 200 ? $this['data'] : null
        ];
    }
}

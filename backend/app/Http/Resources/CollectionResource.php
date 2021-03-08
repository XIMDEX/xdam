<?php

namespace App\Http\Resources;

class CollectionResource extends BaseResource
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
            'id' => $this->id,
            'name' => $this->name,
            'resources' => ResourceResource::collection($this->resources()->get())
        ];
    }
}

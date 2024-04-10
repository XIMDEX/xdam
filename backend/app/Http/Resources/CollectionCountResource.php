<?php

namespace App\Http\Resources;

class CollectionCountResource extends BaseResource
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
            'coll_resource_count' => $this->resources()->count(),
            'resource_type' => $this->accept,
            'max_num_file' => $this->max_number_of_files ?? -1
        ];
    }
}

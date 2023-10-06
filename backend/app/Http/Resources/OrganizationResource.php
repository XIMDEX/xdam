<?php

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $org_resource_count = 0;
        $collections = $this->collections()->get();
        foreach ($collections as $coll) {
            $org_resource_count += count($coll->resources()->count());
        }
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'org_resource_count' => $org_resource_count,
            //'workspaces' => WorkspaceResource::collection($this->workspaces()->get()),
            'abilities' => Auth::user()->abilitiesOnEntity($this->id, Organization::class),
            'collections' => CollectionCountResource::collection($collections)
        ];
    }
}

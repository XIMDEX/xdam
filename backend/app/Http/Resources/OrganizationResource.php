<?php

namespace App\Http\Resources;

use App\Models\Ability;
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
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            //'workspaces' => WorkspaceResource::collection($this->workspaces()->get()),
            'abilities' => Auth::user()->abilitiesOnEntity($this->id, Organization::class),
            'collections' => $this->collections()->get(),
        ];
    }
}

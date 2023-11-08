<?php

namespace App\Http\Resources;

use App\Models\Workspace;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class WorkspaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'wsp_resource_count' => $this->resources()->count(),
            'organization_id' => $this->organization_id,
            'type' => $this->type,
            'abilities' => Auth::user()->abilitiesOnEntity($this->id, Workspace::class)
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'name' => $this->name,
            'current_org_wsp' => [
                //'org' => $this->selected_organization,
                'wsp' => $this->selected_workspace,
            ],
            'organizations' => OrganizationResource::collection($this->organizations()->get()),
            'workspaces' => WorkspaceResource::collection($this->workspaces()->get()),
            'abilities' => $this->getAbilities()
        ];
    }
}
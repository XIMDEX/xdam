<?php

namespace App\Http\Resources;

use App\Models\Workspace;
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
            'current_workspace' => $this->selected_workspace,
            'selected_org_data' => new OrganizationResource(Workspace::find($this->selected_workspace)->organization()->first()),
            'organizations' => OrganizationResource::collection($this->organizations()->get()),
        ];
    }
}

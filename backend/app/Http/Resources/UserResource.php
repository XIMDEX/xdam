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
        $organization_sel = Workspace::find($this->selected_workspace)->organization()->first();
        return  [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'current_workspace' => $this->selected_workspace,
            'selected_org_data' => new OrganizationResource($organization_sel),
            'organizations' => OrganizationResource::collection($this->organizations()->get()),
            'workspaces' => WorkspaceInfoResource::collection(Workspace::where('organization_id', $organization_sel->id)->get())
        ];
    }
}

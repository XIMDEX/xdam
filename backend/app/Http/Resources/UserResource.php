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
            'organizations' => [],
            'workspaces' => [],
            'roles' => RoleResource::collection($this->roles()->get()),
            'permissions' => PermissionResource::collection($this->getPermissionsViaRoles())
        ];
    }
}

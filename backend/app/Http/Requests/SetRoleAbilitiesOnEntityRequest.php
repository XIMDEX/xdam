<?php

namespace App\Http\Requests;

use App\Enums\Entities;
use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Exception;
use Illuminate\Foundation\Http\FormRequest;

class SetRoleAbilitiesOnEntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize()
    {
        //prevent to execute on superadmin
        $this->role_id == (new Roles)->SUPER_ADMIN_ID() ? $this->unauthorize() : null;

        //canno't set or unset roles on you
        $this->user()->id == $this->user_id ? $this->unauthorize() : null;

        //Checks if the user to set abilities is related to entity or has permission
        $usr = User::find($this->user_id);

        switch ($this->on) {
            case Entities::workspace:
                    $wsp = Workspace::find($this->entity_id);
                    //canno't set role on public entities
                    $wsp->isPublic() ? $this->unauthorize() : null;
                    if (!$usr->organizations()->get()->contains($wsp->organization()->first()->id)) {
                        return false;
                    }
                break;

            case Entities::organization:
                    $org = Organization::find($this->entity_id);
                    //canno't set role on public entities
                    $org->type == OrganizationType::public ? $this->unauthorize() : null;
                    if (!$usr->organizations()->get()->contains($org->id)) {
                        return false;
                    }
                break;

            default:
                throw new Exception("invalid entity");
                break;
        }


        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->on) {
            case Entities::workspace:
                $entity_id_validation = 'required|exists:workspaces,id';
                break;
            case Entities::organization:
                $entity_id_validation = 'required|exists:organizations,id';
                break;
            default:
                throw new Exception("invalid entity");
                break;
        }

        //'in' validator only accepts defined values. In this case: set,unset
        return [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'entity_id' => $entity_id_validation,
            'type' => 'required|in:set,unset',
            'on' => 'required|in:org,wsp',
        ];
    }

    public function unauthorize()
    {
        return false;
    }
}

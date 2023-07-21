<?php

namespace App\Services;

use App\Enums\OrganizationType;
use App\Models\DamResource;
use App\Models\Organization;
use App\Models\Workspace;
use App\Utils\Utils;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Lcobucci\JWT\Decoder;
use Lcobucci\JWT\Token\Parser;

class UserService
{
    private $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    public function user()
    {
        return Auth::user();
    }

    public function resources()
    {
        $workspaces = Auth::user()->workspaces()->get();
        $resources = [];
        foreach ($workspaces as $wsp) {
            foreach ($wsp->resources()->get() as $res) {
                $resources[] = $res;
            }
        }

        return Utils::unique_multidimensional_array($resources, 'id');
    }

    public function resourceInfo(DamResource $dam)
    {
        return $dam->getUserAbilities(Auth::user());
    }

    public function getWorkspaces()
    {
        return Auth::user()->workspaces()->get();
    }

    public function getOrganizations()
    {
        return Auth::user()->organizations()->get();
    }

    public function getWorkspacesOfOrganization($organization_id)
    {
        return Auth::user()->workspaces()->where('organization_id', $organization_id)->get();
    }

    public function selectWorkspace($wid)
    {
        $user = Auth::user();
        $wsp = Workspace::find($wid);
        $wsp_id = $wsp->id ?? null;
        $user->selected_workspace = $wsp_id;
        $user->save();
        return ['org' => ($wsp ? $wsp->organization()->first() : 'personal context'), 'selected workspace' => $wsp];
    }

    public function attachResourceToCollection($cid, $rid, $oid): DamResource
    {
        $res = DamResource::find($rid);
        $res->collection_id = $cid;
        $res->save();
        $org_corporate_wsp = Organization::where('id', $oid)->first();

        $org_corporate_wsp->type == OrganizationType::public ?
            $wsp = $org_corporate_wsp->publicWorkspace() :
            $wsp = $org_corporate_wsp->corporateWorkspace();

        $this->resourceService->setResourceWorkspace($res, $wsp);
        return $res;
    }

    public function attachResourceToWorkspace($rid): DamResource
    {
        $resource = DamResource::find($rid);
        $workspace = Workspace::find(Auth::user()->selected_workspace);
        $this->resourceService->setResourceWorkspace($resource, $workspace);
        //TODO: attach to some collection

        return $resource;
    }
}

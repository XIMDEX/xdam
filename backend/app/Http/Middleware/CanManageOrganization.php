<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\PermissionService;

class CanManageOrganization
{
    protected $permissionService;
    public function __construct(PermissionService $permissionService){
        $this->permissionService = $permissionService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $org = $request->organization ?? Organization::find($request->organization_id);
        $user =  Auth::user();

        if ($this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin()) {
            return $next($request);
        }
        return response()->json(['error_org' => 'Unauthorized.'], 401);

        if ($user->canAny([Abilities::MANAGE_ORGANIZATION, Abilities::CREATE_WORKSPACE, Abilities::MANAGE_ORGANIZATION_WORKSPACES], $org) ||  $user->isA(Roles::SUPER_ADMIN)) {
            return $next($request);
        }
        return response()->json(['error_org' => 'Unauthorized.'], 401);
    }
}

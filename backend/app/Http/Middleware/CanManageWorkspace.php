<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\PermissionService;

class CanManageWorkspace
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
        $wsp = Workspace::find($request->workspace_id);
        $user = Auth::user();
        
        if ($this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin()) {
            return $next($request);
        }
        return response()->json(['error_wsp' => 'Unauthorized.'], 401);
        $user_can_manage_the_organization_of_wsp = $user->canAny([Abilities::MANAGE_ORGANIZATION, Abilities::MANAGE_ORGANIZATION_WORKSPACES], $wsp->organization()->first());
        $user_can_manage_the_workspace = $user->canAny([Abilities::MANAGE_WORKSPACE, Abilities::UPDATE_WORKSPACE], $wsp);

        if ($user_can_manage_the_workspace || $user->isA(Roles::SUPER_ADMIN) || $user_can_manage_the_organization_of_wsp) {
            return $next($request);
        }

        return response()->json(['error_wsp' => 'Unauthorized.'], 401);

    }
}

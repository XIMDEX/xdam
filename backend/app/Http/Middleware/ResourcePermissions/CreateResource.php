<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\PermissionService;

class CreateResource
{
    private PermissionService $permissionService;

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
        
        $user = Auth::user();

        if(!$workspace = Workspace::find($user->selected_workspace)) {
            return response()->json(['Error' => 'no workspace selected.'], 401);
        }
        if($this->permissionService->canCreate() || $this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin() || $workspace->isPublic()) {
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 401);
        
        return response()->json([Abilities::CREATE_RESOURCE => 'Error: Unauthorized.'], 401);
    }
}

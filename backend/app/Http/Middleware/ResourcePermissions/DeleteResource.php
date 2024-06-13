<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\PermissionService;

class DeleteResource
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
        //authorize provisionally
        if($this->permissionService->canRemove() || $this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin() ){
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 401);

        return $request->damResource->userIsAuthorized(Auth::user(), Abilities::REMOVE_RESOURCE) ? $next($request) : response()->json([Abilities::REMOVE_RESOURCE => 'Error: Unauthorized.'], 401);
    }
}

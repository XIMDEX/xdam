<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\PermissionService;

class UpdateResourceCard
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
        if($this->permissionService->canUpdate() || $this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin()){
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 401);
        return $request->damResource->userIsAuthorized(Auth::user(), Abilities::UPDATE_RESOURCE_CARD) ? $next($request) : response()->json([Abilities::UPDATE_RESOURCE_CARD => 'Error: Unauthorized.'], 401);
    }
}

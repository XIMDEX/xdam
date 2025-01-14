<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Utils\PermissionCalc;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\PermissionService;

class DownloadResource
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
        
        if($this->permissionService->canRead() || $this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin() ){
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 401);
        return $request->damResource->userIsAuthorized(Auth::user(), Abilities::DOWNLOAD_RESOURCE) ? $next($request) : response()->json([Abilities::DOWNLOAD_RESOURCE => 'Error: Unauthorized.'], 401);

    }
}

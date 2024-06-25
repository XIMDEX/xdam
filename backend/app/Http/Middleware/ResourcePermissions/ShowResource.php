<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Utils\DamUrlUtil;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\PermissionService;

class ShowResource
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
       // return $next($request);
        if($this->permissionService->canRead() || $this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin() ){
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 401);
        $damResource = isset($request->damUrl) ? DamUrlUtil::getResourceFromUrl($request->damUrl) : $request->damResource;
        return $damResource->userIsAuthorized(Auth::user(), Abilities::READ_RESOURCE) ? $next($request) : response()->json([Abilities::REMOVE_RESOURCE => 'Error: Unauthorized.'], 401);


    }
    /*
        Route::get('/render/{damUrl}/{size}', [ResourceController::class, 'render'])->name('damResource.renderWithSize');
        Route::get('/render/{damUrl}',        [ResourceController::class, 'render'])->name('damResource.render');
        Route::get('/{damResource}',          [ResourceController::class, 'get'])   ->name('damResource.get');
     */
}

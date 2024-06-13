<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Models\Permissions;
use Lib\Xrole\Services\PermissionService;

class ReadWorkspace
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

        $user = Auth::user();

        if ($this->permissionService->canRead()|| $this->permissionService->isAdmin() || $this->permissionService->isSuperAdmin()) {
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 401);
        if(!$workspace = Workspace::find($user->selected_workspace)) {
            return response()->json(['Error' => 'No workspace selected.'], 401);
        }

        $can_manage_organization_of_workspace = $user->canAny([Abilities::MANAGE_ORGANIZATION, Abilities::MANAGE_ORGANIZATION_WORKSPACES], $workspace->organization()->first());
        $can_read_workspace_or_manage_workspace = $user->canAny([Abilities::READ_WORKSPACE, Abilities::MANAGE_WORKSPACE], $workspace);

        if($can_manage_organization_of_workspace || $can_read_workspace_or_manage_workspace) {
            return $next($request);
        }
        return response()->json([Abilities::READ_WORKSPACE => 'Error: Unauthorized.'], 401);
    }
    /*
        Route::get('/listTypes', [ResourceController::class, 'listTypes'])->name('damResource.listTypes');
        Route::get('/',          [ResourceController::class, 'getAll'])->name('damResource.getAll');
    */
}

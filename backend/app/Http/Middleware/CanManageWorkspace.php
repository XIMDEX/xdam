<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanManageWorkspace
{
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

        $user_can_manage_the_organization_of_wsp = $user->canAny([Abilities::MANAGE_ORGANIZATION, Abilities::MANAGE_ORGANIZATION_WORKSPACES], $wsp->organization()->first());
        $user_can_manage_the_workspace = $user->canAny([Abilities::MANAGE_WORKSPACE, Abilities::UPDATE_WORKSPACE], $wsp);

        if ($user_can_manage_the_workspace || $user->isA(Roles::SUPER_ADMIN) || $user_can_manage_the_organization_of_wsp) {
            return $next($request);
        }

        return response()->json(['error_wsp' => 'Unauthorized.'], 401);

    }
}

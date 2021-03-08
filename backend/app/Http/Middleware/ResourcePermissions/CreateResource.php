<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Enums\WorkspaceType;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateResource
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
        $user = Auth::user();

        // Provisionally, the user can create any resource, within a collection
        return $next($request);

        if(!$workspace = Workspace::find($user->selected_workspace)) {
            return response()->json(['Error' => 'no workspace selected.'], 401);
        }

        if($user->can(Abilities::CREATE_RESOURCE, $workspace) || $workspace->type == WorkspaceType::public) {
            return $next($request);
        }
        return response()->json([Abilities::CREATE_RESOURCE => 'Error: Unauthorized.'], 401);
    }
}

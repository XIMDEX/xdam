<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Models\DamResource;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeleteResource
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
        $workspace = Workspace::find($user->selected_workspace);
        if($user->can(Abilities::REMOVE_RESOURCE, $workspace)) {
            return $next($request);
        }
        return response()->json(['delete_resource_error' => 'Unauthorized.'], 401);
    }
}

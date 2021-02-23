<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Models\Organization;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanManageRoles
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
        $entity = null;
        $user->isAn('admin') ? $next($request) : null;
        $userAbilities = $user->getAbilities();
        $request->on == 'org' ? $entity = Organization::find($request->wo_id) : $entity = Workspace::find($request->wo_id);

        return $user->can(Abilities::canManageRoles, $entity) ? $next($request) : response()->json(['error_role' => 'Unauthorized.'], 401);
    }
}

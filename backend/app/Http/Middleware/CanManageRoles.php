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

        if($user->isAn('admin')) {
            return $next($request);
        }
        $entity = null;


        if($request->on == 'org') {
            $entity = Organization::find($request->wo_id);
        } else {
            $entity = Workspace::find($request->wo_id);
        }


        if($user->can(Abilities::canManageRoles, $entity))
            return $next($request);


        return response()->json(['error' => 'Unauthorized.'], 401);
    }
}

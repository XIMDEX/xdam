<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanManageOrganization
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
        $org = Organization::find($request->organization_id);
        $user =  Auth::user();

        if ($user->can(Abilities::canManageOrganization, $org) ||  $user->isAn('admin')) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized.'], 401);
    }
}

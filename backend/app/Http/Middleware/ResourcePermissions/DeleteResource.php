<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
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
        //authorize provisionally
        return $next($request);

        return $request->damResource->userIsAuthorized(Auth::user(), Abilities::REMOVE_RESOURCE) ? $next($request) : response()->json([Abilities::REMOVE_RESOURCE => 'Error: Unauthorized.'], 401);
    }
}

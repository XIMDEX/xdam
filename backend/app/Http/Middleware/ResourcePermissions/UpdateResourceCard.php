<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateResourceCard
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
        //return PermissionCalc::check($request, Auth::user(), Abilities::UPDATE_RESOURCE_CARD) ? $next($request) : response()->json([Abilities::UPDATE_RESOURCE_CARD => 'Error: Unauthorized.'], 401);
        return $next($request);
    }
}

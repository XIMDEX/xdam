<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Utils\PermissionCalc;
use Closure;
use Illuminate\Http\Request;

class DeleteResourceCard
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
        //return PermissionCalc::check($request, Auth::user(), Abilities::REMOVE_RESOURCE_CARD) ? $next($request) : response()->json([Abilities::REMOVE_RESOURCE_CARD => 'Error: Unauthorized.'], 401);
        return $next($request);
    }
}

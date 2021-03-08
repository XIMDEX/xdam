<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return $next($request);

        return $request->damResource->userIsAuthorized(Auth::user(), Abilities::UPDATE_RESOURCE_CARD) ? $next($request) : response()->json([Abilities::UPDATE_RESOURCE_CARD => 'Error: Unauthorized.'], 401);
    }
}

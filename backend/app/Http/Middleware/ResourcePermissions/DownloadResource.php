<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Utils\PermissionCalc;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadResource
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

        return $request->damResource->userIsAuthorized(Auth::user(), Abilities::DOWNLOAD_RESOURCE) ? $next($request) : response()->json([Abilities::DOWNLOAD_RESOURCE => 'Error: Unauthorized.'], 401);

    }
}

<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Utils\DamUrlUtil;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShowResource
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
        // provisionally everyone has access to render resources
        return $next($request);

        $damResource = isset($request->damUrl) ? DamUrlUtil::getResourceFromUrl($request->damUrl) : $request->damResource;
        return $damResource->userIsAuthorized(Auth::user(), Abilities::READ_RESOURCE) ? $next($request) : response()->json([Abilities::REMOVE_RESOURCE => 'Error: Unauthorized.'], 401);


    }
    /*
        Route::get('/render/{damUrl}/{size}', [ResourceController::class, 'render'])->name('damResource.renderWithSize');
        Route::get('/render/{damUrl}',        [ResourceController::class, 'render'])->name('damResource.render');
        Route::get('/{damResource}',          [ResourceController::class, 'get'])   ->name('damResource.get');
     */
}

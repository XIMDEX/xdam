<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Utils\PermissionCalc;
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
        //return PermissionCalc::check($request, Auth::user(), Abilities::READ_RESOURCE) ? $next($request) : response()->json([Abilities::READ_RESOURCE => 'Error: Unauthorized.'], 401);
        return $next($request);
    }
    /*
        Route::get('/render/{damUrl}/{size}', [ResourceController::class, 'render'])->name('damResource.renderWithSize');
        Route::get('/render/{damUrl}',        [ResourceController::class, 'render'])->name('damResource.render');
        Route::get('/{damResource}',          [ResourceController::class, 'get'])   ->name('damResource.get');
     */
}

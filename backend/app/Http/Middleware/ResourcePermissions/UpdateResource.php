<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Models\DamResource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateResource
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
        if($request->damResource) {
            $resource = $request->damResource;
        } else {
            $resource = DamResource::find($request->resource_id);
        }
        if($user->can(Abilities::UPDATE_RESOURCE, $resource) || $user->ownResource($resource)) {
            return $next($request);
        }
        return response()->json(['update_resource_error' => 'Unauthorized.'], 401);
    }
}
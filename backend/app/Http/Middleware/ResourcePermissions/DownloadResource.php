<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Models\DamResource;
use App\Models\Media;
use App\Utils\DamUrlUtil;
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
        $user = Auth::user();
        $resource_id = Media::find(DamUrlUtil::decodeUrl($request->damResource))->model_id;
        $resource = DamResource::find($resource_id);
        if($user->can(Abilities::DownloadResource, $resource) || $user->ownResource($resource)) {
            return $next($request);
        }
        return response()->json(['download_resource_error' => 'Unauthorized.'], 401);
    }
}

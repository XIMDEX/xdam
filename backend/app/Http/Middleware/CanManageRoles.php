<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Enums\Entities;
use App\Enums\Roles;
use App\Models\DamResource;
use App\Models\Organization;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanManageRoles
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
        $entity = null;
        $user->isAn(Roles::super_admin) ? $next($request) : null;

        switch ($request->on) {
            case Entities::organization:
                $entity = Organization::find($request->entity_id);
                break;
            case Entities::workspace:
                $entity = Workspace::find($request->entity_id);
                break;
            case Entities::resource:
                $entity = DamResource::find($request->entity_id);
                break;
            default:
                return response()->json(['error_role_invalid_entity' => 'Unauthorized.'], 401);
                break;
        }

        if ($entity instanceof DamResource && $user->ownResource($entity)) {
            return $next($request);
        }

        return $user->can(Abilities::ManageRoles, $entity) ? $next($request) : response()->json(['error_role' => 'Unauthorized.'], 401);
    }
}

<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanManageOrganization
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
        $org = Organization::find($request->organization_id);
        $user =  Auth::user();
        if ($user->can(Abilities::ManageOrganization, $org) ||  $user->isAn(Roles::super_admin) ||  $org->type == OrganizationType::public) {
            return $next($request);
        }

        return response()->json(['error_org' => 'Unauthorized.'], 401);
    }
}

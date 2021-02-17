<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\BouncerFacade as Bouncer;

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

        if($user->isAn('admin')) {
            return $next($request);
        }
        $abilities = $user->getAbilities();

        foreach ($abilities as $ability) {
            if($user->can($ability['name'], $org))
                return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 401);
    }
}

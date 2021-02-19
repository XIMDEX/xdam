<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanManageWorkspace
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
        $wsp = Workspace::find($request->workspace_id);
        $user = Auth::user();

        if ($user->can(Abilities::canManageWorkspace, $wsp) ||  $user->isAn('admin')) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized.'], 401);
    }
}

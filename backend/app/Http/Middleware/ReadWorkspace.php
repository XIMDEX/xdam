<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadWorkspace
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
        $workspace = Workspace::find($user->selected_workspace);
        if($user->canAny([Abilities::READ_WORKSPACE, Abilities::MANAGE_WORKSPACE], $workspace)) {
            return $next($request);
        }
        return response()->json(['read_workspace_error' => 'Unauthorized.'], 401);
    }
    /*
        Route::get('/listTypes', [ResourceController::class, 'listTypes'])->name('damResource.listTypes');
        Route::get('/',          [ResourceController::class, 'getAll'])->name('damResource.getAll');
    */
}

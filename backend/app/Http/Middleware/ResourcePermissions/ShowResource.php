<?php

namespace App\Http\Middleware\ResourcePermissions;

use App\Enums\Abilities;
use App\Models\DamResource;
use App\Models\User;
use App\Models\Workspace;
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
        $user = Auth::user();
        if(isset($request->damUrl)) {
            //'request with damUrl. Get the damResource::class'
            //
        }

        //get all workspaces where the resource is
        $workspaces_where_resource_is = $request->damResource->workspaces()->get();

        //now get all abilities that the user has in each workspace
        $user_abilities_in_workspaces = [];
        foreach ($workspaces_where_resource_is as $wsp) {
            foreach ($user->abilitiesOnEntity($wsp->id, Workspace::class) as $abilities) {
                $user_abilities_in_workspaces[] = $abilities->toArray();
            }
        }

        //then authorize if any of these abilities match with ShowResource
        //this means that the user has te showResource ability in this or some other workspace where the resource is attached.
        foreach ($user_abilities_in_workspaces as $ability) {
            if($ability['name'] == Abilities::READ_RESOURCE) {
                return $next($request);
            }
        }

        return response()->json(['read_resource_error' => 'Unauthorized.'], 401);
    }
    /*
        Route::get('/render/{damUrl}/{size}', [ResourceController::class, 'render'])->name('damResource.renderWithSize');
        Route::get('/render/{damUrl}',        [ResourceController::class, 'render'])->name('damResource.render');
        Route::get('/{damResource}',          [ResourceController::class, 'get'])   ->name('damResource.get');
     */
}

<?php

namespace App\Http\Middleware;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddAutomaticCollection
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

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            $request->request->add(['collection_id' => 6]);
        }

        return $next($request);

    }
}

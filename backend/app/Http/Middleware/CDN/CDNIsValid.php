<?php

namespace App\Http\Middleware\CDN;

use App\Models\CDN;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CDNIsValid
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
        $cdn = CDN::where('id', $request->cdn_code)
                ->first();
        
        if ($cdn === null)
            return response()->json(['error' => 'The CDN doesn\'t exist'], Response::HTTP_UNAUTHORIZED);

        return $next($request);
    }
}

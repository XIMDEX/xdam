<?php

namespace App\Http\Middleware\CDN;

use App\Models\CDN;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCDNAccess
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
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $originURL = $request->headers->get('referer');
        $cdn = CDN::where('id', $request->cdn_code)
                ->first();

        if ($cdn === null)
            return response()->json(['error' => 'The CDN doesn\'t exist'], Response::HTTP_UNAUTHORIZED);

        if (!$cdn->checkAccessRequirements(['ipAddress' => $ipAddress, 'originURL' => $originURL]))
            return response()->json(['error' => 'You can\'t access this CDN.'], Response::HTTP_UNAUTHORIZED);
        
        return $next($request);
    }
}

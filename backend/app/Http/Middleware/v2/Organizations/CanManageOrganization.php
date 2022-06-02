<?php
declare(strict_types=1);

namespace App\Http\Middleware\v2\Organizations;

use Illuminate\Http\Request;
use Closure;

final class CanManageOrganization
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
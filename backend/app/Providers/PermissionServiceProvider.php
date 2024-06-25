<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Lib\Xrole\Models\FirebaseJwt;
use Lib\Xrole\Models\Permissions;
use Lib\Xrole\Services\JwtService;
use Lib\Xrole\Services\PermissionService;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind JwtService into the service container as a singleton
        $this->app->singleton(JwtService::class, function ($app) {
            $publicKeyPath = base_path('lib/xrole/oauth-public.key'); 
            $publicKeyContents = file_get_contents($publicKeyPath);
            $firebase = new FirebaseJwt($publicKeyContents);
            return new JwtService($firebase);
        });

        $this->app->bind(PermissionService::class, function ($app) {
            if(!request()->bearerToken()) {
                return new PermissionService(new Permissions(0b00000000));
            }
            $jwtService = $app->make(JwtService::class);
            $token = $jwtService->verifyToken(request()->bearerToken());
            $permission = (array) $token['p'] ?? [];
            if ($permission) {
                $parts = explode('#', $permission["FA08"][0]);
                $number = (int) $parts[0];
                $permissions = new Permissions($number);
                return new PermissionService($permissions);
            }


            return new PermissionService(new Permissions(0b00000000));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

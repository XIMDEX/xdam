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
            // Assuming JwtService requires a public key path or other dependencies
            //$publicKeyPath = $app['config']->get('services.jwt.public_key');
            $publicKeyPath = '/../../../lib/xrole/oauth-public.key';
            $firebase = new FirebaseJwt($publicKeyPath);
            return new JwtService($firebase);
        });
        // Bind a factory method for PermissionService into the service container
        $this->app->bind(PermissionService::class, function ($app) {
            // Assuming you have JwtService bound in the container
            $jwtService = $app->make(JwtService::class);
            $token = $jwtService->verifyToken(request()->bearerToken());

            if ($token) {
                $permissions = new Permissions('00000010');
                return new PermissionService($permissions);
            }

            // Handle the case where there is no token or return a default PermissionService
            return new PermissionService(new Permissions(0b00000011));
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

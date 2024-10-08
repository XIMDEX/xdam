<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:1800,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,

        'cdn.validCDN' => \App\Http\Middleware\CDN\CDNIsValid::class,
        'cdn.checkCDNAccess' => \App\Http\Middleware\CDN\CheckCDNAccess::class,

        'manage.organizations' => \App\Http\Middleware\CanManageOrganization::class,
        'manage.roles' => \App\Http\Middleware\CanManageRoles::class,
        'manage.workspaces' => \App\Http\Middleware\CanManageWorkspace::class,

        'read.workspace' => \App\Http\Middleware\ReadWorkspace::class,
        'create.resource' => \App\Http\Middleware\ResourcePermissions\CreateResource::class,
        'show.resource' => \App\Http\Middleware\ResourcePermissions\ShowResource::class,
        'download.resource' => \App\Http\Middleware\ResourcePermissions\DownloadResource::class,
        'update.resource' => \App\Http\Middleware\ResourcePermissions\UpdateResource::class,
        'delete.resource' => \App\Http\Middleware\ResourcePermissions\DeleteResource::class,
        'collection.automatic' => \App\Http\Middleware\AddAutomaticCollection::class,
        'update.resource.card' => \App\Http\Middleware\ResourcePermissions\UpdateResourceCard::class,
        'delete.resource.card' => \App\Http\Middleware\ResourcePermissions\DeleteResourceCard::class,
        'collection.automatic' => \App\Http\Middleware\AddAutomaticCollection::class,

        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}

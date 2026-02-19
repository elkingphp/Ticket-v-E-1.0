<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Modules\Core\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'module_status' => \Modules\Core\Http\Middleware\CheckModuleStatus::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            '2fa.mandatory' => \App\Http\Middleware\TwoFactorMandatory::class,
            'profile.complete' => \App\Http\Middleware\EnsureProfileCompleted::class,
            'health.token' => \App\Http\Middleware\HealthCheckToken::class,
            'sudo' => \Modules\Core\Http\Middleware\VerifySudoMode::class,
            'ermo_trace' => \Modules\Core\Http\Middleware\TraceModuleLifecycle::class,
        ]);

        $middleware->group('enforce.security', [
            \App\Http\Middleware\EnsureProfileCompleted::class,
            \App\Http\Middleware\TwoFactorMandatory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            return back()->with('error', __('You do not have permission to access this page.'));
        });
    })->create();
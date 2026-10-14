
<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Register Admin Middleware
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        // MySQL is the only database. When XAMPP MySQL is stopped or the
        // .env database settings are wrong, show a clear message.
        $exceptions->render(function (QueryException $e) {
            $cannotConnect = preg_match(
                '/SQLSTATE\[HY000\] \[(1044|1045|1049|2002|2003|2005|2006)\]/',
                $e->getMessage()
            );

            if (! $cannotConnect) {
                return null;
            }

            return response()->view('errors.database-unavailable', [
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 503);
        });

    })->create();

<?php

use App\Http\Middleware\EnsureCanManageFinances;
use App\Http\Middleware\EnsureCanManageHabits;
use App\Http\Middleware\EnsureCanManageFood;
use App\Http\Middleware\EnsureCanManageRoutines;
use App\Http\Middleware\EnsureCanManageShopping;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'can.manage.finances' => EnsureCanManageFinances::class,
            'can.manage.habits' => EnsureCanManageHabits::class,
            'can.manage.food' => EnsureCanManageFood::class,
            'can.manage.routines' => EnsureCanManageRoutines::class,
            'can.manage.shopping' => EnsureCanManageShopping::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\ExportHarianKeExcel;
use Illuminate\Console\Scheduling\Schedule;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withCommands([
        ExportHarianKeExcel::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('transaksi:export-harian')->dailyAt('23:59');
        $schedule->command('transaksi:export-mingguan')->weeklyOn(7, '23:59');
    })
    ->create();

<?php

// =========================
// Updated exceptions + dedicated log file (dedicated_exceptions.log) with JSON + color-coded console
// File: bootstrap/app_exceptions_logging.php
// =========================

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exceptions\ApiException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\RoleCheck::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // $exceptions->report(function (\Throwable $e, Request $request) {

        //     $timestamp = Carbon::now()->format('Y-m-d H:i:s.u');

        //     $context = [
        //         'exception' => get_class($e),
        //         'message'   => $e->getMessage(),
        //         'file'      => $e->getFile() . ':' . $e->getLine(),
        //         'url'       => $request->fullUrl(),
        //         'ip'        => $request->ip(),
        //         'user_id'   => optional($request->user())->id,
        //         'trace'     => $e->getTraceAsString(),
        //     ];

        //     $level = 'error';
        //     $color = "\e[31m"; // default red for errors
        //     if ($e instanceof ApiException) {
        //         $level = 'info';
        //         $color = "\e[33m"; // yellow for API exceptions
        //     }

        //     // JSON log for dedicated file (machine-friendly)
        //     Log::channel('dedicated_exceptions')->$level(json_encode(array_merge([
        //         'timestamp' => $timestamp,
        //         'level' => strtoupper($level),
        //         'short' => $e->getMessage()
        //     ], $context), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        //     // Colored short log for console/stderr
        //     $coloredMessage = "{$color}[{$timestamp}] {$level}: {$e->getMessage()}\e[0m";
        //     Log::channel('dedicated_exceptions_console')->$level($coloredMessage, $context);
        // });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $message = '404 Not Found';
            if ($request->is('api/*') && $e->getPrevious() instanceof ModelNotFoundException) {
                $model = preg_replace('/[^a-zA-Z]/', '', Str::afterLast($e->getPrevious()->getMessage(), '\\'));
                $message = $model . ' not found';
            }

            $timestamp = Carbon::now()->format('Y-m-d H:i:s.u');
            Log::channel('dedicated_exceptions')->info(json_encode([
                'timestamp'=>$timestamp,
                'level'=>'INFO',
                'message'=>$message,
                'url'=>$request->fullUrl(),
                'ip'=>$request->ip(),
                'user_id'=>optional($request->user())->id
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            if ($request->is('api/*')) return response()->json(['message'=>$message],404);
            return response()->view('errors.404',[],404);
        });

        // $exceptions->render(function (ApiException $e, Request $request) {
        //     $timestamp = Carbon::now()->format('Y-m-d H:i:s.u');
        //     Log::channel('dedicated_exceptions')->info(json_encode([
        //         'timestamp'=>$timestamp,
        //         'level'=>'INFO',
        //         'message'=>$e->getMessage(),
        //         'url'=>$request->fullUrl()
        //     ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        //     return response()->json(['status'=>'error','message'=>$e->getMessage()], $e->status());
        // });

        // $exceptions->render(function (\Throwable $e, Request $request) {
        //     $timestamp = Carbon::now()->format('Y-m-d H:i:s.u');
        //     Log::channel('dedicated_exceptions')->critical(json_encode([
        //         'timestamp'=>$timestamp,
        //         'level'=>'CRITICAL',
        //         'message'=>$e->getMessage(),
        //         'exception'=>get_class($e),
        //         'file'=>$e->getFile().':'.$e->getLine(),
        //         'url'=>$request->fullUrl(),
        //         'ip'=>$request->ip(),
        //         'user_id'=>optional($request->user())->id,
        //         'trace'=>$e->getTraceAsString()
        //     ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        //     $coloredMessage = "\e[35m[{$timestamp}] CRITICAL: {$e->getMessage()}\e[0m"; // magenta
        //     Log::channel('dedicated_exceptions_console')->critical($coloredMessage);

        //     if ($request->is('api/*')) return response()->json(['message'=>'Server error. Please try later.'],500);
        //     return response()->view('errors.500',[],500);
        // });
    })->create();
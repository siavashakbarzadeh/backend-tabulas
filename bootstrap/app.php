<?php

use App\Facades\Api\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->wantsJson()) {
                return ApiResponse::message(trans('exceptions.validation'), Response::HTTP_UNPROCESSABLE_ENTITY)
                    ->hasError()
                    ->setErrors($e->errors())
                    ->send();
            }
            return $e;
        });
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->wantsJson()) {
                return ApiResponse::message(trans('exceptions.access_denied_http'), Response::HTTP_FORBIDDEN)
                    ->hasError()
                    ->send();
            }
            return $e;
        });
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->wantsJson()) {
                return ApiResponse::message(trans('exceptions.not_found_http'), Response::HTTP_NOT_FOUND)
                    ->hasError()
                    ->send();
            }
            return $e;
        });
    })->create();

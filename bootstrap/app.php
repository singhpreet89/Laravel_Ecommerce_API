<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, $request){
            if (file_exists(storage_path('framework/maintenance.php'))) {
                return response()->json([
                    'message' => 'Service is under maintenance.',
                    'errors' => ['server' => ['The service is temporarily unavailable. Please try again later.']],
                ], Response::HTTP_SERVICE_UNAVAILABLE); 
            }
        
            return null; // Let Laravel handle other exceptions
        });

        $exceptions->renderable(function (ModelNotFoundException $e, $request) {
            $model = strtolower(class_basename($e->getModel()));
            return response()->json([
                'message' => 'Model not found.',
                'errors' => [$model => ["No {$model} found with the provided id."]],
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'message' => 'Invalid URL.',
                'errors' => ['url' => [$request->fullUrl() . ' is invalid.']],
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'message' => 'Method not allowed.',
                'errors' => ['url' => ["The {$request->method()} method is not supported for {$request->fullUrl()}"]],
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        });

        $exceptions->renderable(function (AuthorizationException $e, $request) {
            return response()->json([
                'message' => 'Not authorized.',
                'errors' => ['user' => [$e->getMessage()]],
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->renderable(function (QueryException $e, $request) {
            $errorCode = $e->errorInfo[1] ?? null;

            return match ($errorCode) {
                1062 => response()->json([
                    'message' => 'Resource conflict.',
                    'errors' => ['resource' => ['This resource already exists.']],
                ], Response::HTTP_CONFLICT),

                1451 => response()->json([
                    'message' => 'Resource conflict.',
                    'errors' => ['resource' => ['This resource is linked and cannot be deleted.']],
                ], Response::HTTP_CONFLICT),

                default => null,
            };
        });

        /**
         * If the exception is not one of the exceptions listed above
         * i.e. Database not connected 
         * ! IF -- The application is in DEBUG mode
         *      Display the Detailed ERROR
         * ! ELSE -- In all other cases, send the Internal Server Error
         */
        $exceptions->renderable(function (Throwable $e, $request) {
            // If app is in debug mode, let Laravel handle it (shows stack trace / Whoops page in local/dev)
            if (config('app.debug')) {
                throw $e; // rethrow the exception to let Laravel handle it as default
            }
        
            // Otherwise, show a clean internal server error
            return response()->json([
                'message' => 'Internal server error.',
                'errors' => [
                    'server' => ['Please try again.']
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();

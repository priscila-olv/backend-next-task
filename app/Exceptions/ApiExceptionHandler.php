<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;

class ApiExceptionHandler extends ExceptionHandler
{
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return parent::render($request, $exception);
    }
}

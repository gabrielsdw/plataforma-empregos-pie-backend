<?php

declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

abstract class BaseController
{
    protected function defaultJsonResponse(
        mixed $data = null,
        ?string $message = null,
        int $statusCode = 200,
        array|null $errors = null
    ): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'statusCode' => $statusCode,
            'errors' => $errors,
        ], $statusCode);
    }

    protected function error(string $message, int $status = 400, array|null $errors = null, ?\Exception $exception = null): JsonResponse
    {
        if ($status === 500) {
            // Log the error message for internal server errors
            Log::error('Internal Server Error: ', [
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'exception' => $exception,
            ]);
        }

        return $this->defaultJsonResponse(null, $message, $status, $errors);
    }
}

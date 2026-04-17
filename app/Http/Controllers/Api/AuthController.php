<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\Api\AuthRepository;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
    public function __construct(private AuthRepository $repository)
    {
        $this->repository = $repository;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $response = $this->repository->login($request->validated());
            if($this->repository::$hasError) {
                return $this->error($this->repository::$message, $this->repository::$statusCode);
            }

            return $this->defaultJsonResponse(
                $response,
                $this->repository::$message,
                $this->repository::$statusCode,
            );
        } catch (\Exception $e) {
            return $this->error('Internal server error', 500, exception: $e);
        }
    }
}

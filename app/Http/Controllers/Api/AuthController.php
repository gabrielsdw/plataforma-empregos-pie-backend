<?php

declare(strict_types=1);
namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterBusinessRequest;
use App\Http\Requests\Auth\RegisterCandidateRequest;
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

    public function registerCandidate(RegisterCandidateRequest $request): JsonResponse
    {
        try {
            $response = $this->repository->registerCandidate($request->validated());

            return $this->defaultJsonResponse(
                $response,
                $this->repository::$message,
                $this->repository::$statusCode,
            );
        } catch (\Exception $e) {
            return $this->error('Internal server error', 500, exception: $e);
        }
    }

    public function registerBusiness(RegisterBusinessRequest $request): JsonResponse
    {
        try {
            $response = $this->repository->registerBusiness($request->validated());

            return $this->defaultJsonResponse(
                $response,
                $this->repository::$message,
                $this->repository::$statusCode,
            );
        } catch (\Exception $e) {
            return $this->error('Internal server error', 500, exception: $e);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->repository->logout();

            return $this->defaultJsonResponse(
                null,
                $this->repository::$message,
                $this->repository::$statusCode,
            );
        } catch (\Exception $e) {
            return $this->error('Internal server error', 500, exception: $e);
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            $response = $this->repository->refresh();

            return $this->defaultJsonResponse(
                $response,
                $this->repository::$message,
                $this->repository::$statusCode,
            );
        } catch (\Exception $e) {
            return $this->error('Internal server error', 500, exception: $e);
        }
    }

    public function me(): JsonResponse
    {
        try {
            $response = $this->repository->me();

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

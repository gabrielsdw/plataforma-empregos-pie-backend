<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Vacancy\ApplyToVacancyRequest;
use App\Http\Requests\Vacancy\StoreVacancyRequest;
use App\Repositories\Api\VacancyRepository;
use Illuminate\Http\JsonResponse;

class VacancyController extends BaseController
{
    public function __construct(private VacancyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        try {
            $response = $this->repository->listForAuthenticatedBusiness();

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

    public function show(int $vacancyId): JsonResponse
    {
        try {
            $response = $this->repository->showForAuthenticatedBusiness($vacancyId);

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

    public function published(): JsonResponse
    {
        try {
            $response = $this->repository->listPublished();

            return $this->defaultJsonResponse(
                $response,
                $this->repository::$message,
                $this->repository::$statusCode,
            );
        } catch (\Exception $e) {
            return $this->error('Internal server error', 500, exception: $e);
        }
    }

    public function applicants(): JsonResponse
    {
        try {
            $response = $this->repository->listApplicantsForAuthenticatedBusiness();

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

    public function myApplications(): JsonResponse
    {
        try {
            $response = $this->repository->listForAuthenticatedCandidate();

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

    public function store(StoreVacancyRequest $request): JsonResponse
    {
        try {
            $response = $this->repository->create($request->validated());

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

    public function update(StoreVacancyRequest $request, int $vacancyId): JsonResponse
    {
        try {
            $response = $this->repository->updateForAuthenticatedBusiness(
                $vacancyId,
                $request->validated()
            );

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

    public function close(int $vacancyId): JsonResponse
    {
        try {
            $response = $this->repository->closeForAuthenticatedBusiness($vacancyId);

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

    public function apply(ApplyToVacancyRequest $request, int $vacancyId): JsonResponse
    {
        try {
            $response = $this->repository->applyForAuthenticatedCandidate(
                $vacancyId,
                $request->validated()
            );

            if ($this->repository::$hasError) {
                return $this->error(
                    $this->repository::$message,
                    $this->repository::$statusCode
                );
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

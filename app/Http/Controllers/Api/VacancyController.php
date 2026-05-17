<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Vacancy\StoreVacancyRequest;
use App\Repositories\Api\VacancyRepository;
use Illuminate\Http\JsonResponse;

class VacancyController extends BaseController
{
    public function __construct(private VacancyRepository $repository)
    {
        $this->repository = $repository;
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
}

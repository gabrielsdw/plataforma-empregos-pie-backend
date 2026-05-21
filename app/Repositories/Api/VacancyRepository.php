<?php

declare(strict_types=1);

namespace App\Repositories\Api;

use App\Models\Vacancy;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class VacancyRepository extends BaseRepository
{
    public function listForAuthenticatedBusiness(): Collection|null
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'business') {
            static::$hasError = true;
            static::$message = 'Only authenticated businesses can view their vacancies';
            static::$statusCode = 403;

            return null;
        }

        $vacancies = Vacancy::query()
            ->where('business_id', $user->id)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        static::$hasError = false;
        static::$message = 'Vacancies fetched successfully';
        static::$statusCode = 200;

        return $vacancies;
    }

    public function listPublished(): Collection
    {
        $vacancies = Vacancy::query()
            ->with('business:id,name,company_name')
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        static::$hasError = false;
        static::$message = 'Published vacancies fetched successfully';
        static::$statusCode = 200;

        return $vacancies;
    }

    public function create(array $data): Vacancy|null
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'business') {
            static::$hasError = true;
            static::$message = 'Only authenticated businesses can publish vacancies';
            static::$statusCode = 403;

            return null;
        }

        $status = $data['status'] ?? 'published';

        $vacancy = Vacancy::query()->create([
            'business_id' => $user->id,
            'title' => $data['title'],
            'employment_type' => $data['employment_type'],
            'location' => $data['location'],
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'description' => $data['description'],
            'requirements' => $data['requirements'],
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ])->load('business:id,name,email,company_name');

        static::$hasError = false;
        static::$message = $status === 'draft'
            ? 'Vacancy draft saved successfully'
            : 'Vacancy published successfully';
        static::$statusCode = 201;

        return $vacancy;
    }
}

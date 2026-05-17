<?php

declare(strict_types=1);

namespace App\Repositories\Api;

use App\Models\Vacancy;
use App\Repositories\BaseRepository;

class VacancyRepository extends BaseRepository
{
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

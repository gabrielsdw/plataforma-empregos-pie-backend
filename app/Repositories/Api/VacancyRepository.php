<?php

declare(strict_types=1);

namespace App\Repositories\Api;

use App\Models\Vacancy;
use App\Models\VacancyApplication;
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
            ->withCount('applications')
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
        $user = auth('api')->user();
        $appliedVacancyIds = collect();

        if ($user && $user->role === 'candidate') {
            $appliedVacancyIds = VacancyApplication::query()
                ->where('candidate_id', $user->id)
                ->pluck('vacancy_id');
        }

        $vacancies = Vacancy::query()
            ->with('business:id,name,company_name')
            ->withCount('applications')
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        $vacancies->each(function (Vacancy $vacancy) use ($appliedVacancyIds) {
            $vacancy->setAttribute(
                'has_applied',
                $appliedVacancyIds->contains($vacancy->id)
            );
        });

        static::$hasError = false;
        static::$message = 'Published vacancies fetched successfully';
        static::$statusCode = 200;

        return $vacancies;
    }

    public function listApplicantsForAuthenticatedBusiness(): Collection|null
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'business') {
            static::$hasError = true;
            static::$message = 'Only authenticated businesses can view applicants';
            static::$statusCode = 403;

            return null;
        }

        $applications = VacancyApplication::query()
            ->with([
                'candidate:id,name,email,phone,resume_path',
                'vacancy:id,business_id,title',
            ])
            ->whereHas('vacancy', function ($query) use ($user) {
                $query->where('business_id', $user->id);
            })
            ->orderByDesc('applied_at')
            ->orderByDesc('created_at')
            ->get();

        static::$hasError = false;
        static::$message = 'Applicants fetched successfully';
        static::$statusCode = 200;

        return $applications;
    }

    public function listForAuthenticatedCandidate(): Collection|null
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'candidate') {
            static::$hasError = true;
            static::$message = 'Only authenticated candidates can view their applications';
            static::$statusCode = 403;

            return null;
        }

        $applications = VacancyApplication::query()
            ->with([
                'vacancy:id,business_id,title,location,employment_type,status,published_at',
                'vacancy.business:id,name,company_name',
            ])
            ->where('candidate_id', $user->id)
            ->orderByDesc('applied_at')
            ->orderByDesc('created_at')
            ->get();

        static::$hasError = false;
        static::$message = 'Applications fetched successfully';
        static::$statusCode = 200;

        return $applications;
    }

    public function showForAuthenticatedBusiness(int $vacancyId): Vacancy|null
    {
        $vacancy = $this->findOwnedByAuthenticatedBusiness($vacancyId);

        if (!$vacancy) {
            return null;
        }

        static::$hasError = false;
        static::$message = 'Vacancy fetched successfully';
        static::$statusCode = 200;

        return $vacancy;
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

        $vacancy->setAttribute('applications_count', 0);

        static::$hasError = false;
        static::$message = $status === 'draft'
            ? 'Vacancy draft saved successfully'
            : 'Vacancy published successfully';
        static::$statusCode = 201;

        return $vacancy;
    }

    public function updateForAuthenticatedBusiness(int $vacancyId, array $data): Vacancy|null
    {
        $vacancy = $this->findOwnedByAuthenticatedBusiness($vacancyId);

        if (!$vacancy) {
            return null;
        }

        $status = $data['status'] ?? $vacancy->status;
        $publishedAt = $vacancy->published_at;

        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = now();
        }

        if ($status === 'draft') {
            $publishedAt = null;
        }

        $vacancy->update([
            'title' => $data['title'],
            'employment_type' => $data['employment_type'],
            'location' => $data['location'],
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'description' => $data['description'],
            'requirements' => $data['requirements'],
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        static::$hasError = false;
        static::$message = $status === 'draft'
            ? 'Vacancy draft updated successfully'
            : 'Vacancy updated successfully';
        static::$statusCode = 200;

        return $vacancy->fresh()->load('business:id,name,email,company_name');
    }

    public function closeForAuthenticatedBusiness(int $vacancyId): Vacancy|null
    {
        $vacancy = $this->findOwnedByAuthenticatedBusiness($vacancyId);

        if (!$vacancy) {
            return null;
        }

        $vacancy->update([
            'status' => 'closed',
        ]);

        static::$hasError = false;
        static::$message = 'Vacancy closed successfully';
        static::$statusCode = 200;

        return $vacancy->fresh()->load('business:id,name,email,company_name');
    }

    public function applyForAuthenticatedCandidate(int $vacancyId, array $data): VacancyApplication|null
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'candidate') {
            static::$hasError = true;
            static::$message = 'Only authenticated candidates can apply to vacancies';
            static::$statusCode = 403;

            return null;
        }

        $vacancy = Vacancy::query()
            ->where('id', $vacancyId)
            ->where('status', 'published')
            ->first();

        if (!$vacancy) {
            static::$hasError = true;
            static::$message = 'Vacancy not found';
            static::$statusCode = 404;

            return null;
        }

        $alreadyApplied = VacancyApplication::query()
            ->where('vacancy_id', $vacancy->id)
            ->where('candidate_id', $user->id)
            ->exists();

        if ($alreadyApplied) {
            static::$hasError = true;
            static::$message = 'You have already applied to this vacancy';
            static::$statusCode = 409;

            return null;
        }

        $application = VacancyApplication::query()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $user->id,
            'phone' => $data['phone'],
            'portfolio_url' => $data['portfolio_url'] ?? null,
            'cover_letter' => $data['cover_letter'],
            'status' => 'applied',
            'applied_at' => now(),
        ])->load([
            'candidate:id,name,email,phone,resume_path',
            'vacancy:id,title,business_id',
        ]);

        static::$hasError = false;
        static::$message = 'Application submitted successfully';
        static::$statusCode = 201;

        return $application;
    }

    private function findOwnedByAuthenticatedBusiness(int $vacancyId): Vacancy|null
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'business') {
            static::$hasError = true;
            static::$message = 'Only authenticated businesses can manage vacancies';
            static::$statusCode = 403;

            return null;
        }

        $vacancy = Vacancy::query()
            ->where('id', $vacancyId)
            ->where('business_id', $user->id)
            ->first();

        if (!$vacancy) {
            static::$hasError = true;
            static::$message = 'Vacancy not found';
            static::$statusCode = 404;

            return null;
        }

        return $vacancy;
    }
}

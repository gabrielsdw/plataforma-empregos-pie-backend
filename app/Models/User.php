<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'phone',
        'company_name',
        'cnpj',
        'website',
        'resume_path',
        'resume_original_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'resume_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function getResumeUrlAttribute(): ?string
    {
        if (!$this->resume_path) {
            return null;
        }

        return Storage::disk('public')->url($this->resume_path);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'business_id');
    }

    public function vacancyApplications(): HasMany
    {
        return $this->hasMany(VacancyApplication::class, 'candidate_id');
    }
}

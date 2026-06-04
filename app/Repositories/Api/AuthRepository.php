<?php

declare(strict_types=1);
namespace App\Repositories\Api;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AuthRepository extends BaseRepository
{
    public function login(array $data)
    {
        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        if (!empty($data['audience'])) {
            $credentials['role'] = $data['audience'];
        }

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            static::$hasError = true;
            static::$message = 'Invalid credentials';
            static::$statusCode = 401;

            return null;
        }

        static::$hasError = false;
        static::$message = 'Login successful';
        static::$statusCode = 200;

        return $this->tokenPayload($token);
    }

    public function registerCandidate(array $data): array
    {
        $resumePath = null;
        $resumeOriginalName = null;

        if (isset($data['resume']) && $data['resume'] instanceof UploadedFile) {
            $resumePath = $data['resume']->store('resumes', 'public');
            $resumeOriginalName = $data['resume']->getClientOriginalName();
        }

        $user = User::query()->create([
            'role' => 'candidate',
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'resume_path' => $resumePath,
            'resume_original_name' => $resumeOriginalName,
        ]);

        $token = auth('api')->login($user);

        static::$hasError = false;
        static::$message = 'Candidate registered successfully';
        static::$statusCode = 201;

        return $this->tokenPayload($token);
    }

    public function registerBusiness(array $data): array
    {
        $user = User::query()->create([
            'role' => 'business',
            'name' => $data['company_name'],
            'company_name' => $data['company_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'cnpj' => $data['cnpj'],
            'website' => $data['website'] ?? null,
        ]);

        $token = auth('api')->login($user);

        static::$hasError = false;
        static::$message = 'Business registered successfully';
        static::$statusCode = 201;

        return $this->tokenPayload($token);
    }

    public function getResumeForAuthenticatedCandidate(): ?array
    {
        $user = auth('api')->user();

        if (!$user instanceof User) {
            static::$hasError = true;
            static::$message = 'Unauthenticated';
            static::$statusCode = 401;

            return null;
        }

        if ($user->role !== 'candidate') {
            static::$hasError = true;
            static::$message = 'Only authenticated candidates can download their resume';
            static::$statusCode = 403;

            return null;
        }

        $resumePath = $user->resume_path;

        if (!$resumePath) {
            static::$hasError = true;
            static::$message = 'Resume not found';
            static::$statusCode = 404;

            return null;
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($resumePath)) {
            static::$hasError = true;
            static::$message = 'Resume file not found';
            static::$statusCode = 404;

            return null;
        }

        static::$hasError = false;
        static::$message = 'Resume downloaded successfully';
        static::$statusCode = 200;

        return [
            'disk' => $disk,
            'path' => $resumePath,
            'download_name' => $user->resume_original_name ?: basename($resumePath),
        ];
    }

    public function updateProfile(array $data): User|null
    {
        $user = auth('api')->user();

        if (!$user instanceof User) {
            static::$hasError = true;
            static::$message = 'Unauthenticated';
            static::$statusCode = 401;

            return null;
        }

        if ($user->role === 'business') {
            $user->update([
                'name' => $data['company_name'],
                'company_name' => $data['company_name'],
                'email' => $data['email'],
                'website' => $data['website'] ?? null,
            ]);
        } else {
            $updates = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ];

            if (isset($data['resume']) && $data['resume'] instanceof UploadedFile) {
                if ($user->resume_path && Storage::disk('public')->exists($user->resume_path)) {
                    Storage::disk('public')->delete($user->resume_path);
                }

                $updates['resume_path'] = $data['resume']->store('resumes', 'public');
                $updates['resume_original_name'] = $data['resume']->getClientOriginalName();
            }

            $user->update($updates);
        }

        static::$hasError = false;
        static::$message = 'Profile updated successfully';
        static::$statusCode = 200;

        return $user->fresh();
    }

    public function logout(): bool
    {
        auth('api')->logout();

        static::$hasError = false;
        static::$message = 'Successfully logged out';
        static::$statusCode = 200;

        return true;
    }

    public function refresh(): array
    {
        $token = auth('api')->refresh();

        static::$hasError = false;
        static::$message = 'Token refreshed';
        static::$statusCode = 200;

        return $this->tokenPayload($token);
    }

    public function me(): mixed
    {
        static::$hasError = false;
        static::$message = 'Authenticated user';
        static::$statusCode = 200;

        return auth('api')->user();
    }

    private function tokenPayload(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user(),
        ];
    }
}

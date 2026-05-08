<?php

declare(strict_types=1);
namespace App\Repositories\Api;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Http\UploadedFile;

class AuthRepository extends BaseRepository
{
    public function login(array $data)
    {
        $token = auth('api')->attempt($data);

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

        if (isset($data['resume']) && $data['resume'] instanceof UploadedFile) {
            $resumePath = $data['resume']->store('resumes', 'public');
        }

        $user = User::query()->create([
            'role' => 'candidate',
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'resume_path' => $resumePath,
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

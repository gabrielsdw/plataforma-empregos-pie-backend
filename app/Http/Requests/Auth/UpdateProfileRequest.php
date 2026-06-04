<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth('api')->user();

        $commonEmailRules = [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($user?->id),
        ];

        if ($user?->role === 'business') {
            return [
                'company_name' => ['required', 'string', 'min:2', 'max:255'],
                'email' => $commonEmailRules,
                'website' => ['nullable', 'url', 'max:255'],
            ];
        }

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => $commonEmailRules,
            'phone' => ['required', 'string', 'min:14', 'max:20'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}

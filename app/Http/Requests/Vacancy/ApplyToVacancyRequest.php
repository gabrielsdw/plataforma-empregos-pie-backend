<?php

declare(strict_types=1);

namespace App\Http\Requests\Vacancy;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyToVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'min:14', 'max:20'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'cover_letter' => ['required', 'string', 'min:20'],
        ];
    }
}

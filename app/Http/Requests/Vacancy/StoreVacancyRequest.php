<?php

declare(strict_types=1);

namespace App\Http\Requests\Vacancy;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVacancyRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'employment_type' => [
                'required',
                'string',
                Rule::in(['clt', 'pj', 'estagio', 'temporario']),
            ],
            'location' => ['required', 'string', 'max:255'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'description' => ['required', 'string'],
            'requirements' => ['required', 'string'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published'])],
        ];
    }
}

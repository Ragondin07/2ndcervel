<?php

namespace App\Http\Requests;

use App\Support\MvpOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(MvpOptions::PROJECT_STATUSES))],
            'priority' => ['required', Rule::in(array_keys(MvpOptions::PRIORITIES))],
            'category' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre du projet est obligatoire.',
            'status.in' => 'Le statut du projet est invalide.',
            'priority.in' => 'La priorite du projet est invalide.',
        ];
    }
}

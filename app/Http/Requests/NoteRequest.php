<?php

namespace App\Http\Requests;

use App\Support\MvpOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', Rule::in(array_keys(MvpOptions::NOTE_TYPES))],
            'status' => ['required', Rule::in(array_keys(MvpOptions::NOTE_STATUSES))],
            'source_type' => ['nullable', 'string', 'max:255'],
            'source_detail' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la note est obligatoire.',
            'content.required' => 'Le contenu de la note est obligatoire.',
            'project_id.exists' => 'Le projet selectionne n existe pas.',
            'type.in' => 'Le type de note est invalide.',
            'status.in' => 'Le statut de note est invalide.',
        ];
    }
}

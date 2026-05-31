<?php

namespace App\Http\Requests;

use App\Support\MvpOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class QuickAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_type' => ['required', Rule::in(array_keys(MvpOptions::QUICK_ADD_TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $status = $this->input('status');
                $allowed = match ($this->input('content_type')) {
                    'note' => array_keys(MvpOptions::NOTE_STATUSES),
                    'decision' => array_keys(MvpOptions::DECISION_STATUSES),
                    'action' => array_keys(MvpOptions::ACTION_STATUSES),
                    default => [],
                };

                if ($status && ! in_array($status, $allowed, true)) {
                    $validator->errors()->add('status', 'Le statut choisi ne correspond pas au type de contenu.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'content_type.required' => 'Choisissez le type de contenu a creer.',
            'content_type.in' => 'Le type de contenu est invalide.',
            'title.required' => 'Le titre est obligatoire.',
            'content.required' => 'Le contenu principal est obligatoire.',
            'project_id.exists' => 'Le projet selectionne n existe pas.',
        ];
    }
}

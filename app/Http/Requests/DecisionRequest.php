<?php

namespace App\Http\Requests;

use App\Models\Note;
use App\Support\MvpOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'source_note_id' => ['nullable', 'integer', Rule::exists('notes', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'decision' => ['required', 'string'],
            'justification' => ['nullable', 'string'],
            'alternatives' => ['nullable', 'string'],
            'risks' => ['nullable', 'string'],
            'impact' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(MvpOptions::DECISION_STATUSES))],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $projectId = $this->input('project_id');
                $sourceNoteId = $this->input('source_note_id');

                if (! $projectId || ! $sourceNoteId) {
                    return;
                }

                $note = Note::query()->find($sourceNoteId);

                if ($note && (string) $note->project_id !== (string) $projectId) {
                    $validator->errors()->add('source_note_id', 'La note source doit appartenir au projet choisi.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la decision est obligatoire.',
            'decision.required' => 'Le contenu de la decision est obligatoire.',
            'project_id.exists' => 'Le projet selectionne n existe pas.',
            'source_note_id.exists' => 'La note source selectionnee n existe pas.',
            'status.in' => 'Le statut de decision est invalide.',
        ];
    }
}

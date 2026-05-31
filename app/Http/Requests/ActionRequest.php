<?php

namespace App\Http\Requests;

use App\Models\Decision;
use App\Models\Note;
use App\Support\MvpOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'note_id' => ['nullable', 'integer', Rule::exists('notes', 'id')],
            'decision_id' => ['nullable', 'integer', Rule::exists('decisions', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(MvpOptions::ACTION_STATUSES))],
            'priority' => ['required', Rule::in(array_keys(MvpOptions::PRIORITIES))],
            'due_date' => ['nullable', 'date'],
            'return_to' => ['nullable', 'url'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $projectId = $this->input('project_id');

                if ($projectId && $this->input('note_id')) {
                    $note = Note::query()->find($this->input('note_id'));
                    if ($note && (string) $note->project_id !== (string) $projectId) {
                        $validator->errors()->add('note_id', 'La note liee doit appartenir au projet choisi.');
                    }
                }

                if ($projectId && $this->input('decision_id')) {
                    $decision = Decision::query()->find($this->input('decision_id'));
                    if ($decision && (string) $decision->project_id !== (string) $projectId) {
                        $validator->errors()->add('decision_id', 'La decision liee doit appartenir au projet choisi.');
                    }
                }
            },
        ];
    }
}

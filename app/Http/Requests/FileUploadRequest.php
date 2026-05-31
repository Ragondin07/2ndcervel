<?php

namespace App\Http\Requests;

use App\Models\Note;
use App\Support\MvpOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FileUploadRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'uploads' => ['required', 'array', 'min:1'],
            'uploads.*' => ['required', 'file', 'max:'.$this->maxUploadSizeKb()],
            'return_to' => ['nullable', 'url'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $projectId = $this->input('project_id');
                $noteId = $this->input('note_id');

                if ($projectId && $noteId) {
                    $note = Note::query()->find($noteId);

                    if ($note && (string) $note->project_id !== (string) $projectId) {
                        $validator->errors()->add('note_id', 'La note liee doit appartenir au projet choisi.');
                    }
                }

                foreach ($this->file('uploads', []) as $upload) {
                    $extension = Str::lower($upload->getClientOriginalExtension());

                    if (! in_array($extension, MvpOptions::ALLOWED_FILE_EXTENSIONS, true)) {
                        $validator->errors()->add('uploads', "Le format .{$extension} n est pas accepte.");
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'uploads.required' => 'Ajoutez au moins un fichier.',
            'uploads.array' => 'Le champ fichiers est invalide.',
            'uploads.*.file' => 'Un des elements envoyes n est pas un fichier valide.',
            'uploads.*.max' => 'Un fichier depasse la taille maximale autorisee.',
            'project_id.exists' => 'Le projet selectionne n existe pas.',
            'note_id.exists' => 'La note selectionnee n existe pas.',
        ];
    }

    private function maxUploadSizeKb(): int
    {
        return ((int) config('filesystems.max_upload_size_mb', 50)) * 1024;
    }
}

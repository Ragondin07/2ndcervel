<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Jobs\ExtractOcrJob;
use App\Jobs\IndexFileJob;
use App\Models\File as StoredFile;
use App\Models\Note;
use App\Models\Project;
use App\Services\OcrExtractor;
use App\Support\ProjectActivity;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class FileController extends Controller
{
    public function index(): View
    {
        return view('files.index', [
            'files' => StoredFile::query()
                ->with(['project', 'note'])
                ->whereNull('archived_at')
                ->when(request()->boolean('pinned'), fn ($query) => $query->where('is_pinned', true))
                ->latest('updated_at')
                ->get(),
            'projects' => $this->projects(),
        ]);
    }

    public function create(): View
    {
        return view('files.create', [
            'projects' => $this->projects(),
            'notes' => $this->notes(),
            'selectedProjectId' => request('project_id'),
            'returnTo' => request('return_to', request('project_id') ? route('projects.show', request('project_id')) : route('files.index')),
            'maxUploadSizeMb' => $this->maxUploadSizeMb(),
        ]);
    }

    public function store(FileUploadRequest $request): RedirectResponse
    {
        $storedPaths = [];
        $data = $this->normalizedData($request->validated());
        $returnTo = $data['return_to'] ?? null;
        unset($data['return_to']);

        try {
            $files = DB::transaction(function () use ($request, $data, &$storedPaths): EloquentCollection {
                $created = new EloquentCollection();

                foreach ($request->file('uploads') as $upload) {
                    $file = $this->storeUploadedFile($upload, $data, $storedPaths);
                    $created->push($file);
                }

                return $created;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('uploads')->delete($path);
            }

            Log::error('File upload failed.', [
                'user_id' => $request->user()?->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        Log::info('Files uploaded.', [
            'count' => $files->count(),
            'file_ids' => $files->pluck('id')->all(),
            'project_id' => $data['project_id'] ?? null,
            'note_id' => $data['note_id'] ?? null,
            'user_id' => $request->user()?->id,
        ]);

        $files->each(fn (StoredFile $file) => ProjectActivity::logCreated($file, 'file_created', $request->user()?->id));

        $files->each(function (StoredFile $file): void {
            IndexFileJob::dispatch($file->id);

            if (OcrExtractor::supportsExtension($file->extension)) {
                ExtractOcrJob::dispatch($file->id);
            }
        });

        return redirect()
            ->to($returnTo ?: route('files.index'))
            ->with('status', $files->count() > 1 ? "{$files->count()} fichiers ajoutes." : 'Fichier ajoute.');
    }

    public function show(StoredFile $file): View
    {
        $file->load(['project', 'note']);

        return view('files.show', [
            'file' => $file,
            'projects' => $this->projects(),
        ]);
    }

    public function download(StoredFile $file): StreamedResponse
    {
        abort_unless(Storage::disk('uploads')->exists($file->path), 404);

        return Storage::disk('uploads')->download($file->path, $file->original_name);
    }


    public function updateProject(StoredFile $file): RedirectResponse
    {
        $data = request()->validate([
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ], [
            'project_id.exists' => 'Le projet selectionne n existe pas.',
        ]);

        $file->update(['project_id' => $data['project_id'] ?? null]);
        $file->load('project');
        ProjectActivity::logCreated($file, 'file_created', request()->user()?->id);

        Log::info('File project changed.', [
            'file_id' => $file->id,
            'project_id' => $file->project_id,
            'user_id' => request()->user()?->id,
        ]);

        return back()->with('status', $file->project_id ? 'Fichier deplace vers le projet.' : 'Fichier retire du projet.');
    }

    public function bulkUpdateProject(): RedirectResponse
    {
        $data = request()->validate([
            'file_ids' => ['required', 'array', 'min:1'],
            'file_ids.*' => ['integer', 'exists:files,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ], [
            'file_ids.required' => 'Selectionnez au moins un fichier.',
            'project_id.exists' => 'Le projet selectionne n existe pas.',
        ]);

        $files = StoredFile::query()->whereIn('id', $data['file_ids'])->get();
        $projectId = $data['project_id'] ?? null;

        foreach ($files as $file) {
            $file->update(['project_id' => $projectId]);
            $file->load('project');
            ProjectActivity::logCreated($file, 'file_created', request()->user()?->id);
        }

        Log::info('Files bulk project changed.', [
            'file_ids' => $files->pluck('id')->all(),
            'project_id' => $projectId,
            'user_id' => request()->user()?->id,
        ]);

        return back()->with('status', $files->count().' fichier(s) deplace(s).');
    }

    public function reindex(StoredFile $file): RedirectResponse
    {
        $file->update([
            'indexing_status' => 'en_attente',
            'extraction_status' => 'en_attente',
            'extraction_error' => null,
        ]);

        IndexFileJob::dispatch($file->id);
        Log::info('File reindex requested.', ['file_id' => $file->id, 'user_id' => request()->user()?->id]);

        return redirect()
            ->route('files.show', $file)
            ->with('status', 'Indexation relancee.');
    }

    public function retryOcr(StoredFile $file): RedirectResponse
    {
        try {
            if (! OcrExtractor::supportsExtension($file->extension)) {
                $file->update([
                    'ocr_status' => 'non_supporte',
                    'ocr_error' => null,
                ]);
                $file->searchable();

                Log::info('File OCR retry skipped unsupported file.', [
                    'file_id' => $file->id,
                    'extension' => $file->extension,
                    'user_id' => request()->user()?->id,
                ]);

                return redirect()
                    ->route('files.show', $file)
                    ->with('status', 'OCR non supporte pour ce format de fichier.');
            }

            if (! Storage::disk('uploads')->exists($file->path)) {
                $file->update([
                    'ocr_status' => 'erreur',
                    'ocr_error' => 'Fichier original introuvable sur le stockage uploads.',
                ]);

                Log::warning('File OCR retry failed missing upload.', [
                    'file_id' => $file->id,
                    'path' => $file->path,
                    'user_id' => request()->user()?->id,
                ]);

                return redirect()
                    ->route('files.show', $file)
                    ->with('status', 'OCR impossible : fichier original introuvable. Verifiez les uploads et permissions.');
            }

            $file->update([
                'ocr_status' => 'en_attente',
                'ocr_error' => null,
            ]);

            ExtractOcrJob::dispatch($file->id);
            Log::info('File OCR retry requested.', [
                'file_id' => $file->id,
                'queue' => 'indexing',
                'user_id' => request()->user()?->id,
            ]);

            return redirect()
                ->route('files.show', $file)
                ->with('status', 'OCR relance en arriere-plan. Statut : en attente.');
        } catch (Throwable $exception) {
            $file->update([
                'ocr_status' => 'erreur',
                'ocr_error' => $exception->getMessage(),
            ]);

            Log::error('File OCR retry could not be queued.', [
                'file_id' => $file->id,
                'user_id' => request()->user()?->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('files.show', $file)
                ->with('status', 'OCR non relance : une erreur a ete journalisee. Verifiez la file et les permissions.');
        }
    }

    public function destroy(StoredFile $file): RedirectResponse
    {
        $path = $file->path;
        $fileId = $file->id;

        try {
            $file->delete();
            Storage::disk('uploads')->delete($path);
        } catch (QueryException $exception) {
            Log::error('File database deletion failed.', ['file_id' => $fileId, 'error' => $exception->getMessage()]);

            throw $exception;
        }

        Log::warning('File deleted.', ['file_id' => $fileId, 'user_id' => request()->user()?->id]);

        return redirect()
            ->to($returnTo ?: route('files.index'))
            ->with('status', 'Fichier supprime.');
    }

    private function storeUploadedFile(UploadedFile $upload, array $data, array &$storedPaths): StoredFile
    {
        $extension = Str::lower($upload->getClientOriginalExtension());
        $originalName = $upload->getClientOriginalName();
        $storedName = Str::uuid().($extension ? ".{$extension}" : '');
        $directory = $this->storageDirectory($data['project_id'] ?? null);
        $hash = hash_file('sha256', $upload->getRealPath());
        $path = $upload->storeAs($directory, $storedName, 'uploads');
        $storedPaths[] = $path;

        return StoredFile::query()->create([
            'project_id' => $data['project_id'] ?? null,
            'note_id' => $data['note_id'] ?? null,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'path' => $path,
            'mime_type' => $upload->getMimeType(),
            'extension' => $extension,
            'size' => $upload->getSize(),
            'hash' => $hash,
            'description' => $data['description'] ?? null,
            'indexing_status' => 'en_attente',
            'extraction_status' => 'en_attente',
            'ocr_status' => OcrExtractor::supportsExtension($extension) ? 'en_attente' : 'non_supporte',
        ]);
    }

    private function storageDirectory(?int $projectId): string
    {
        $scope = $projectId ? "projects/{$projectId}" : 'inbox';

        return $scope.'/'.now()->format('Y/m');
    }

    private function projects()
    {
        return Project::query()
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function normalizedData(array $data): array
    {
        if (empty($data['project_id']) && ! empty($data['note_id'])) {
            $data['project_id'] = Note::query()->whereKey($data['note_id'])->value('project_id');
        }

        return $data;
    }

    private function notes()
    {
        return Note::query()
            ->with('project')
            ->latest('updated_at')
            ->limit(100)
            ->get(['id', 'project_id', 'title']);
    }

    private function maxUploadSizeMb(): int
    {
        return (int) config('filesystems.max_upload_size_mb', 50);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Decision;
use App\Models\File as StoredFile;
use App\Models\Note;
use App\Models\Project;
use App\Support\MvpOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InboxController extends Controller
{
    public function index(): View
    {
        $notes = Note::query()
            ->whereNull('project_id')
            ->whereNull('archived_at')
            ->where('status', '!=', 'archivee')
            ->latest('updated_at')
            ->get();

        $decisions = Decision::query()
            ->whereNull('project_id')
            ->whereNull('archived_at')
            ->latest('updated_at')
            ->get();

        $actions = Action::query()
            ->whereNull('project_id')
            ->whereNull('archived_at')
            ->latest('updated_at')
            ->get();

        $files = StoredFile::query()
            ->whereNull('project_id')
            ->whereNull('archived_at')
            ->latest('updated_at')
            ->get();

        return view('notes.inbox', [
            'notes' => $notes,
            'decisions' => $decisions,
            'actions' => $actions,
            'files' => $files,
            'recentItems' => $this->recentItems($notes, $decisions, $actions, $files),
            'projects' => $this->projects(),
            'types' => MvpOptions::NOTE_TYPES,
            'counts' => [
                'notes' => $notes->count(),
                'decisions' => $decisions->count(),
                'actions' => $actions->count(),
                'files' => $files->count(),
                'total' => $notes->count() + $decisions->count() + $actions->count() + $files->count(),
            ],
        ]);
    }

    public function assignProject(Request $request, string $type, int $id): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
        ], [
            'project_id.required' => 'Choisissez un projet pour classer cet element.',
            'project_id.exists' => 'Le projet selectionne n existe pas.',
        ]);

        $item = $this->findInboxItem($type, $id);
        $item->update(['project_id' => $data['project_id']]);

        Log::info('Inbox item assigned to project.', [
            'type' => $type,
            'id' => $id,
            'project_id' => $data['project_id'],
            'user_id' => $request->user()?->id,
        ]);

        return back()->with('status', 'Element classe dans le projet.');
    }

    public function updateNoteType(Request $request, Note $note): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(MvpOptions::NOTE_TYPES))],
        ], [
            'type.in' => 'Le type de note est invalide.',
        ]);

        $note->update($data);
        Log::info('Inbox note type updated.', ['note_id' => $note->id, 'type' => $data['type'], 'user_id' => $request->user()?->id]);

        return back()->with('status', 'Type de note mis a jour.');
    }

    public function convertNoteToDecision(Request $request, Note $note): RedirectResponse
    {
        $decision = DB::transaction(function () use ($note): Decision {
            $decision = Decision::query()->create([
                'project_id' => $note->project_id,
                'source_note_id' => $note->id,
                'title' => $note->title,
                'decision' => $note->content,
                'justification' => 'Convertie depuis une note de l Inbox.',
                'status' => 'proposee',
            ]);

            $note->update([
                'status' => 'archivee',
                'archived_at' => now(),
            ]);

            return $decision;
        });

        Log::info('Inbox note converted to decision.', ['note_id' => $note->id, 'decision_id' => $decision->id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('inbox')
            ->with('status', 'Note convertie en decision. La note source a ete archivee.');
    }

    public function convertNoteToAction(Request $request, Note $note): RedirectResponse
    {
        $action = DB::transaction(function () use ($note): Action {
            $action = Action::query()->create([
                'project_id' => $note->project_id,
                'note_id' => $note->id,
                'title' => $note->title,
                'description' => $note->content,
                'status' => 'a_faire',
                'priority' => 'normale',
            ]);

            $note->update([
                'status' => 'archivee',
                'archived_at' => now(),
            ]);

            return $action;
        });

        Log::info('Inbox note converted to action.', ['note_id' => $note->id, 'action_id' => $action->id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('inbox')
            ->with('status', 'Note convertie en action. La note source a ete archivee.');
    }

    public function archive(Request $request, string $type, int $id): RedirectResponse
    {
        $item = $this->findInboxItem($type, $id);
        $updates = ['archived_at' => now()];

        if ($item instanceof Note) {
            $updates['status'] = 'archivee';
        } elseif ($item instanceof Action) {
            $updates['status'] = 'abandonnee';
        } elseif ($item instanceof Decision) {
            $updates['status'] = 'annulee';
        }

        $item->update($updates);

        Log::info('Inbox item archived.', ['type' => $type, 'id' => $id, 'user_id' => $request->user()?->id]);

        return back()->with('status', 'Element archive.');
    }

    public function destroy(Request $request, string $type, int $id): RedirectResponse
    {
        $item = $this->findInboxItem($type, $id);

        if ($item instanceof StoredFile) {
            Storage::disk('uploads')->delete($item->path);
        }

        $item->delete();

        Log::warning('Inbox item deleted.', ['type' => $type, 'id' => $id, 'user_id' => $request->user()?->id]);

        return back()->with('status', 'Element supprime.');
    }

    private function findInboxItem(string $type, int $id): Model
    {
        $model = match ($type) {
            'note' => Note::class,
            'decision' => Decision::class,
            'action' => Action::class,
            'file' => StoredFile::class,
            default => throw new NotFoundHttpException(),
        };

        $query = $model::query()->whereKey($id);

        if (in_array($type, ['note', 'decision', 'action', 'file'], true)) {
            $query->whereNull('project_id');
        }

        if ($type === 'note') {
            $query->whereNull('archived_at')->where('status', '!=', 'archivee');
        } else {
            $query->whereNull('archived_at');
        }

        return $query->firstOrFail();
    }

    private function projects()
    {
        return Project::query()
            ->whereNull('archived_at')
            ->where('status', '!=', 'archive')
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function recentItems($notes, $decisions, $actions, $files)
    {
        return collect()
            ->merge($notes->map(fn (Note $note) => [
                'type' => 'note',
                'label' => 'Note',
                'title' => $note->title,
                'updated_at' => $note->updated_at,
                'url' => route('notes.show', $note),
            ]))
            ->merge($decisions->map(fn (Decision $decision) => [
                'type' => 'decision',
                'label' => 'Decision',
                'title' => $decision->title,
                'updated_at' => $decision->updated_at,
                'url' => route('decisions.show', $decision),
            ]))
            ->merge($actions->map(fn (Action $action) => [
                'type' => 'action',
                'label' => 'Action',
                'title' => $action->title,
                'updated_at' => $action->updated_at,
                'url' => route('actions.show', $action),
            ]))
            ->merge($files->map(fn (StoredFile $file) => [
                'type' => 'file',
                'label' => 'Fichier',
                'title' => $file->original_name,
                'updated_at' => $file->updated_at,
                'url' => route('files.show', $file),
            ]))
            ->sortByDesc('updated_at')
            ->take(10)
            ->values();
    }
}

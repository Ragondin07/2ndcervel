<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use App\Models\Project;
use App\Support\MvpOptions;
use App\Support\ProjectActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(): View
    {
        return view('notes.index', [
            'activeNotes' => Note::query()
                ->with('project')
                ->whereNull('archived_at')
                ->where('status', '!=', 'archivee')
                ->when(request()->boolean('pinned'), fn ($query) => $query->where('is_pinned', true))
                ->latest('updated_at')
                ->get(),
            'archivedNotes' => Note::query()
                ->with('project')
                ->where(function ($query): void {
                    $query->whereNotNull('archived_at')
                        ->orWhere('status', 'archivee');
                })
                ->latest('updated_at')
                ->get(),
            'types' => MvpOptions::NOTE_TYPES,
            'statuses' => MvpOptions::NOTE_STATUSES,
            'pinnedOnly' => request()->boolean('pinned'),
        ]);
    }

    public function inbox(): View
    {
        return view('notes.inbox', [
            'notes' => Note::query()
                ->whereNull('project_id')
                ->whereNull('archived_at')
                ->where('status', '!=', 'archivee')
                ->latest('updated_at')
                ->get(),
            'types' => MvpOptions::NOTE_TYPES,
        ]);
    }

    public function create(): View
    {
        return view('notes.create', [
            'note' => new Note([
                'project_id' => request('project_id'),
                'type' => request('type', 'note_brute'),
                'title' => request('title', ''),
                'status' => 'brouillon',
            ]),
            'projects' => $this->projects(),
            'types' => MvpOptions::NOTE_TYPES,
            'statuses' => MvpOptions::NOTE_STATUSES,
            'returnTo' => request('return_to', request('project_id') ? route('projects.show', request('project_id')) : route('notes.index')),
        ]);
    }

    public function store(NoteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $returnTo = $data['return_to'] ?? null;
        unset($data['return_to']);
        $data['archived_at'] = $data['status'] === 'archivee' ? now() : null;

        $note = Note::query()->create($data);
        Log::info('Note created.', ['note_id' => $note->id, 'project_id' => $note->project_id, 'user_id' => $request->user()?->id]);
        ProjectActivity::logCreated($note, 'note_created', $request->user()?->id);

        return redirect()
            ->to($returnTo ?: route('notes.show', $note))
            ->with('status', 'Note creee.');
    }

    public function show(Note $note): View
    {
        $note->load(['project', 'decisions', 'actions', 'files']);
        $relatedNotes = $note->project
            ? $note->project->notes()->whereKeyNot($note->id)->latest('updated_at')->limit(8)->get()
            : collect();

        return view('notes.show', [
            'note' => $note,
            'types' => MvpOptions::NOTE_TYPES,
            'statuses' => MvpOptions::NOTE_STATUSES,
            'relatedNotes' => $relatedNotes,
        ]);
    }

    public function edit(Note $note): View
    {
        return view('notes.edit', [
            'note' => $note,
            'projects' => $this->projects(),
            'types' => MvpOptions::NOTE_TYPES,
            'statuses' => MvpOptions::NOTE_STATUSES,
            'pinnedOnly' => request()->boolean('pinned'),
        ]);
    }

    public function update(NoteRequest $request, Note $note): RedirectResponse
    {
        $data = $request->validated();
        $returnTo = $data['return_to'] ?? null;
        unset($data['return_to']);

        $oldProjectId = $note->project_id;

        if ($data['status'] === 'archivee' && $note->archived_at === null) {
            $data['archived_at'] = now();
        }

        if ($data['status'] !== 'archivee') {
            $data['archived_at'] = null;
        }

        $note->update($data);

        if ($oldProjectId !== $note->project_id && $note->project_id !== null) {
            $note->load('project');
            ProjectActivity::logCreated($note, 'note_created', $request->user()?->id);
        }

        Log::info('Note updated.', ['note_id' => $note->id, 'project_id' => $note->project_id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->to($returnTo ?: route('notes.show', $note))
            ->with('status', 'Note modifiee.');
    }

    public function archive(Note $note): RedirectResponse
    {
        $note->update([
            'status' => 'archivee',
            'archived_at' => now(),
        ]);
        Log::info('Note archived.', ['note_id' => $note->id, 'user_id' => request()->user()?->id]);

        return redirect()
            ->route('notes.index')
            ->with('status', 'Note archivee.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();
        Log::warning('Note deleted.', ['note_id' => $note->id, 'user_id' => request()->user()?->id]);

        return redirect()
            ->route('notes.index')
            ->with('status', 'Note supprimee.');
    }

    private function projects()
    {
        return Project::query()
            ->orderBy('title')
            ->get(['id', 'title']);
    }
}

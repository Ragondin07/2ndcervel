<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickAddRequest;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Note;
use App\Models\Project;
use App\Support\MvpOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class QuickAddController extends Controller
{
    public function create(): View
    {
        return view('quick-add.create', [
            'projects' => $this->projects(),
            'types' => MvpOptions::QUICK_ADD_TYPES,
            'noteStatuses' => MvpOptions::NOTE_STATUSES,
            'decisionStatuses' => MvpOptions::DECISION_STATUSES,
            'actionStatuses' => MvpOptions::ACTION_STATUSES,
        ]);
    }

    public function store(QuickAddRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return match ($data['content_type']) {
            'note' => $this->storeNote($request, $data),
            'decision' => $this->storeDecision($request, $data),
            'action' => $this->storeAction($request, $data),
        };
    }

    private function storeNote(QuickAddRequest $request, array $data): RedirectResponse
    {
        $note = Note::query()->create([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => 'note_brute',
            'status' => $data['status'],
            'source_type' => 'ajout_rapide',
            'archived_at' => $data['status'] === 'archivee' ? now() : null,
        ]);
        Log::info('Quick note created.', ['note_id' => $note->id, 'project_id' => $note->project_id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('notes.show', $note)
            ->with('status', 'Note creee depuis l ajout rapide.');
    }

    private function storeDecision(QuickAddRequest $request, array $data): RedirectResponse
    {
        $decision = Decision::query()->create([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'decision' => $data['content'],
            'status' => $data['status'],
        ]);
        Log::info('Quick decision created.', ['decision_id' => $decision->id, 'project_id' => $decision->project_id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('decisions.show', $decision)
            ->with('status', 'Decision creee depuis l ajout rapide.');
    }

    private function storeAction(QuickAddRequest $request, array $data): RedirectResponse
    {
        $action = Action::query()->create([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'description' => $data['content'],
            'status' => $data['status'],
            'priority' => 'normale',
            'completed_at' => $data['status'] === 'faite' ? now() : null,
        ]);
        Log::info('Quick action created.', ['action_id' => $action->id, 'project_id' => $action->project_id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('actions.show', $action)
            ->with('status', 'Action creee depuis l ajout rapide.');
    }

    private function projects()
    {
        return Project::query()
            ->whereNull('archived_at')
            ->where('status', '!=', 'archive')
            ->orderBy('title')
            ->get(['id', 'title']);
    }
}

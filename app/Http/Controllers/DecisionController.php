<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecisionRequest;
use App\Models\Decision;
use App\Models\Note;
use App\Models\Project;
use App\Support\MvpOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DecisionController extends Controller
{
    public function index(): View
    {
        $projectId = request('project_id');
        $status = request('status');

        return view('decisions.index', [
            'decisions' => Decision::query()
                ->with('project')
                ->whereNull('archived_at')
                ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest('updated_at')
                ->get(),
            'projects' => $this->projects(),
            'statuses' => MvpOptions::DECISION_STATUSES,
            'filters' => [
                'project_id' => $projectId,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        return view('decisions.create', [
            'decision' => new Decision([
                'status' => 'proposee',
            ]),
            'projects' => $this->projects(),
            'notes' => $this->notes(),
            'statuses' => MvpOptions::DECISION_STATUSES,
        ]);
    }

    public function store(DecisionRequest $request): RedirectResponse
    {
        $decision = Decision::query()->create($this->normalizedData($request->validated()));
        Log::info('Decision created.', ['decision_id' => $decision->id, 'project_id' => $decision->project_id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('decisions.show', $decision)
            ->with('status', 'Decision creee.');
    }

    public function show(Decision $decision): View
    {
        $decision->load(['project', 'sourceNote', 'actions']);

        return view('decisions.show', [
            'decision' => $decision,
            'statuses' => MvpOptions::DECISION_STATUSES,
        ]);
    }

    public function edit(Decision $decision): View
    {
        return view('decisions.edit', [
            'decision' => $decision,
            'projects' => $this->projects(),
            'notes' => $this->notes(),
            'statuses' => MvpOptions::DECISION_STATUSES,
        ]);
    }

    public function update(DecisionRequest $request, Decision $decision): RedirectResponse
    {
        $decision->update($this->normalizedData($request->validated()));
        Log::info('Decision updated.', ['decision_id' => $decision->id, 'project_id' => $decision->project_id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('decisions.show', $decision)
            ->with('status', 'Decision modifiee.');
    }

    public function destroy(Decision $decision): RedirectResponse
    {
        $decision->delete();
        Log::warning('Decision deleted.', ['decision_id' => $decision->id, 'user_id' => request()->user()?->id]);

        return redirect()
            ->route('decisions.index')
            ->with('status', 'Decision supprimee.');
    }

    private function projects()
    {
        return Project::query()
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function notes()
    {
        return Note::query()
            ->with('project')
            ->latest('updated_at')
            ->limit(100)
            ->get(['id', 'project_id', 'title']);
    }

    private function normalizedData(array $data): array
    {
        if (empty($data['project_id']) && ! empty($data['source_note_id'])) {
            $data['project_id'] = Note::query()->whereKey($data['source_note_id'])->value('project_id');
        }

        return $data;
    }

    public static function excerpt(?string $text): string
    {
        return Str::limit($text ?: 'Aucune justification renseignee.', 140);
    }
}

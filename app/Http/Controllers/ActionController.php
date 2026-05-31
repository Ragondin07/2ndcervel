<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActionRequest;
use App\Models\Action;
use App\Models\Project;
use App\Support\MvpOptions;
use App\Support\ProjectActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ActionController extends Controller
{
    public function create(): View
    {
        $projectId = request('project_id');

        return view('actions.create', [
            'action' => new Action([
                'project_id' => $projectId,
                'status' => 'a_faire',
                'priority' => 'normale',
            ]),
            'projects' => $this->projects(),
            'statuses' => MvpOptions::ACTION_STATUSES,
            'priorities' => MvpOptions::PRIORITIES,
            'returnTo' => request('return_to', $projectId ? route('projects.show', $projectId) : route('actions.index')),
        ]);
    }

    public function store(ActionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $returnTo = $data['return_to'] ?? null;
        unset($data['return_to']);
        $data['completed_at'] = $data['status'] === 'faite' ? now() : null;

        $action = Action::query()->create($data);
        ProjectActivity::logCreated($action, 'action_created', $request->user()?->id);

        if ($action->status === 'faite') {
            ProjectActivity::logActionCompleted($action, $request->user()?->id);
        }

        return redirect()
            ->to($returnTo ?: route('actions.show', $action))
            ->with('status', 'Action creee.');
    }

    public function index(): View
    {
        $projectId = request('project_id');
        $status = request('status');
        $due = request('due');

        $actions = Action::query()
            ->with('project')
            ->whereNull('archived_at')
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($due === 'overdue', fn ($query) => $query->whereNotNull('due_date')->whereDate('due_date', '<', today()))
            ->when($due === 'today', fn ($query) => $query->whereDate('due_date', today()))
            ->when(request()->boolean('pinned'), fn ($query) => $query->where('is_pinned', true))
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest('updated_at')
            ->get();

        return view('actions.index', [
            'actions' => $actions,
            'kanban' => $actions->groupBy('status'),
            'projects' => $this->projects(),
            'statuses' => MvpOptions::ACTION_STATUSES,
            'filters' => [
                'project_id' => $projectId,
                'status' => $status,
                'due' => $due,
                'pinned' => request()->boolean('pinned'),
            ],
        ]);
    }

    public function show(Action $action): View
    {
        $action->load(['project', 'note', 'decision']);

        return view('actions.show', [
            'action' => $action,
            'statuses' => MvpOptions::ACTION_STATUSES,
        ]);
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

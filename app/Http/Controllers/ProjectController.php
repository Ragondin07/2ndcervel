<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Support\MvpOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('projects.index', [
            'activeProjects' => Project::query()
                ->whereNull('archived_at')
                ->where('status', '!=', 'archive')
                ->latest('updated_at')
                ->get(),
            'archivedProjects' => Project::query()
                ->where(function ($query): void {
                    $query->whereNotNull('archived_at')
                        ->orWhere('status', 'archive');
                })
                ->latest('updated_at')
                ->get(),
            'statuses' => MvpOptions::PROJECT_STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('projects.create', [
            'project' => new Project([
                'status' => 'idee',
                'priority' => 'normale',
            ]),
            'statuses' => MvpOptions::PROJECT_STATUSES,
            'priorities' => MvpOptions::PRIORITIES,
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['archived_at'] = $data['status'] === 'archive' ? now() : null;

        $project = Project::query()->create($data);
        Log::info('Project created.', ['project_id' => $project->id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Projet cree.');
    }

    public function show(Project $project): View
    {
        $project->load([
            'notes' => fn ($query) => $query->latest('updated_at'),
            'decisions' => fn ($query) => $query->latest('updated_at'),
            'actions' => fn ($query) => $query->latest('updated_at'),
            'files' => fn ($query) => $query->latest('updated_at'),
        ]);

        return view('projects.show', [
            'project' => $project,
            'statuses' => MvpOptions::PROJECT_STATUSES,
            'priorities' => MvpOptions::PRIORITIES,
        ]);
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', [
            'project' => $project,
            'statuses' => MvpOptions::PROJECT_STATUSES,
            'priorities' => MvpOptions::PRIORITIES,
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();

        if ($project->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $project);
        }

        if ($data['status'] === 'archive' && $project->archived_at === null) {
            $data['archived_at'] = now();
        }

        if ($data['status'] !== 'archive') {
            $data['archived_at'] = null;
        }

        $project->update($data);
        Log::info('Project updated.', ['project_id' => $project->id, 'user_id' => $request->user()?->id]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Projet modifie.');
    }

    public function archive(Project $project): RedirectResponse
    {
        $project->update([
            'status' => 'archive',
            'archived_at' => now(),
        ]);
        Log::info('Project archived.', ['project_id' => $project->id, 'user_id' => request()->user()?->id]);

        return redirect()
            ->route('projects.index')
            ->with('status', 'Projet archive.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();
        Log::warning('Project deleted.', ['project_id' => $project->id, 'user_id' => request()->user()?->id]);

        return redirect()
            ->route('projects.index')
            ->with('status', 'Projet supprime.');
    }

    private function uniqueSlug(string $title, ?Project $ignore = null): string
    {
        $base = Str::slug($title) ?: 'projet';
        $slug = $base;
        $index = 2;

        while (Project::query()
            ->where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()
        ) {
            $slug = "{$base}-{$index}";
            $index++;
        }

        return $slug;
    }
}

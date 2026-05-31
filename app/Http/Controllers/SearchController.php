<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Decision;
use App\Models\File;
use App\Models\Note;
use App\Models\Project;
use App\Support\MvpOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class SearchController extends Controller
{
    public const TYPES = [
        'project' => 'projet',
        'note' => 'note',
        'decision' => 'decision',
        'action' => 'action',
        'file' => 'fichier',
    ];

    public function __invoke(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'project_id' => $request->query('project_id'),
            'type' => $request->query('type'),
            'status' => $request->query('status'),
        ];

        return view('search.index', [
            'filters' => $filters,
            'projects' => Project::query()->orderBy('title')->get(['id', 'title']),
            'types' => self::TYPES,
            'statuses' => $this->statuses(),
            'results' => $filters['q'] === '' ? collect() : $this->search($filters),
            'engine' => $filters['q'] === '' ? null : $this->lastEngine,
        ]);
    }

    private ?string $lastEngine = null;

    private function search(array $filters): Collection
    {
        if (config('scout.driver') === 'null') {
            $this->lastEngine = 'sql';

            return $this->searchWithSql($filters);
        }

        try {
            $results = $this->searchWithScout($filters);
            $this->lastEngine = 'meilisearch';

            return $results;
        } catch (Throwable $exception) {
            Log::warning('Meilisearch unavailable, falling back to SQL search.', [
                'error' => $exception->getMessage(),
            ]);

            $this->lastEngine = 'sql';

            return $this->searchWithSql($filters);
        }
    }

    private function searchWithScout(array $filters): Collection
    {
        $type = $filters['type'];
        $results = collect();

        if ($type === null || $type === '' || $type === 'project') {
            $results = $results->merge($this->searchScoutModel(Project::class, $filters, fn (Project $project) => $this->projectResult($project, $filters)));
        }

        if ($type === null || $type === '' || $type === 'note') {
            $results = $results->merge($this->searchScoutModel(Note::class, $filters, fn (Note $note) => $this->noteResult($note, $filters)));
        }

        if ($type === null || $type === '' || $type === 'decision') {
            $results = $results->merge($this->searchScoutModel(Decision::class, $filters, fn (Decision $decision) => $this->decisionResult($decision, $filters)));
        }

        if ($type === null || $type === '' || $type === 'action') {
            $results = $results->merge($this->searchScoutModel(Action::class, $filters, fn (Action $action) => $this->actionResult($action, $filters)));
        }

        if ($type === null || $type === '' || $type === 'file') {
            $results = $results->merge($this->searchScoutModel(File::class, $filters, fn (File $file) => $this->fileResult($file, $filters)));
        }

        return $results
            ->sortByDesc('date')
            ->values()
            ->take(100);
    }

    private function searchScoutModel(string $modelClass, array $filters, callable $mapper): Collection
    {
        $builder = $modelClass::search($filters['q']);

        if ($filters['project_id']) {
            $builder->where('project_id', (int) $filters['project_id']);
        }

        if ($filters['status']) {
            $builder->where('status', $filters['status']);
        }

        return $builder
            ->take(25)
            ->get()
            ->map($mapper);
    }

    private function searchWithSql(array $filters): Collection
    {
        $type = $filters['type'];
        $results = collect();

        if ($type === null || $type === '' || $type === 'project') {
            $results = $results->merge($this->searchProjects($filters));
        }

        if ($type === null || $type === '' || $type === 'note') {
            $results = $results->merge($this->searchNotes($filters));
        }

        if ($type === null || $type === '' || $type === 'decision') {
            $results = $results->merge($this->searchDecisions($filters));
        }

        if ($type === null || $type === '' || $type === 'action') {
            $results = $results->merge($this->searchActions($filters));
        }

        if ($type === null || $type === '' || $type === 'file') {
            $results = $results->merge($this->searchFiles($filters));
        }

        return $results
            ->sortByDesc('date')
            ->values()
            ->take(100);
    }

    private function searchProjects(array $filters): Collection
    {
        return Project::query()
            ->where(fn (Builder $query) => $this->likeAny($query, ['title', 'summary', 'description'], $filters['q']))
            ->when($filters['project_id'], fn (Builder $query) => $query->whereKey($filters['project_id']))
            ->when($filters['status'], fn (Builder $query) => $query->where('status', $filters['status']))
            ->latest('updated_at')
            ->limit(25)
            ->get()
            ->map(fn (Project $project) => $this->projectResult($project, $filters));
    }

    private function searchNotes(array $filters): Collection
    {
        return Note::query()
            ->with('project')
            ->where(fn (Builder $query) => $this->likeAny($query, ['title', 'content'], $filters['q']))
            ->when($filters['project_id'], fn (Builder $query) => $query->where('project_id', $filters['project_id']))
            ->when($filters['status'], fn (Builder $query) => $query->where('status', $filters['status']))
            ->latest('updated_at')
            ->limit(25)
            ->get()
            ->map(fn (Note $note) => $this->noteResult($note, $filters));
    }

    private function searchDecisions(array $filters): Collection
    {
        return Decision::query()
            ->with('project')
            ->where(fn (Builder $query) => $this->likeAny($query, ['title', 'decision', 'justification', 'alternatives', 'risks', 'impact'], $filters['q']))
            ->when($filters['project_id'], fn (Builder $query) => $query->where('project_id', $filters['project_id']))
            ->when($filters['status'], fn (Builder $query) => $query->where('status', $filters['status']))
            ->latest('updated_at')
            ->limit(25)
            ->get()
            ->map(fn (Decision $decision) => $this->decisionResult($decision, $filters));
    }

    private function searchActions(array $filters): Collection
    {
        return Action::query()
            ->with('project')
            ->where(fn (Builder $query) => $this->likeAny($query, ['title', 'description'], $filters['q']))
            ->when($filters['project_id'], fn (Builder $query) => $query->where('project_id', $filters['project_id']))
            ->when($filters['status'], fn (Builder $query) => $query->where('status', $filters['status']))
            ->latest('updated_at')
            ->limit(25)
            ->get()
            ->map(fn (Action $action) => $this->actionResult($action, $filters));
    }

    private function searchFiles(array $filters): Collection
    {
        return File::query()
            ->with('project')
            ->where(fn (Builder $query) => $this->likeAny($query, ['original_name', 'stored_name', 'description'], $filters['q']))
            ->when($filters['project_id'], fn (Builder $query) => $query->where('project_id', $filters['project_id']))
            ->when($filters['status'], fn (Builder $query) => $query->where('indexing_status', $filters['status']))
            ->latest('updated_at')
            ->limit(25)
            ->get()
            ->map(fn (File $file) => $this->fileResult($file, $filters));
    }

    private function projectResult(Project $project, array $filters): array
    {
        return [
            'title' => $project->title,
            'type' => 'projet',
            'project' => $project->title,
            'excerpt' => $this->excerpt([$project->summary, $project->description], $filters['q']),
            'date' => $project->updated_at,
            'url' => route('projects.show', $project),
        ];
    }

    private function noteResult(Note $note, array $filters): array
    {
        return [
            'title' => $note->title,
            'type' => 'note',
            'project' => $note->project?->title ?? 'Inbox',
            'excerpt' => $this->excerpt([$note->content], $filters['q']),
            'date' => $note->updated_at,
            'url' => route('notes.show', $note),
        ];
    }

    private function decisionResult(Decision $decision, array $filters): array
    {
        return [
            'title' => $decision->title,
            'type' => 'decision',
            'project' => $decision->project?->title ?? 'Non classee',
            'excerpt' => $this->excerpt([$decision->decision, $decision->justification], $filters['q']),
            'date' => $decision->updated_at,
            'url' => route('decisions.show', $decision),
        ];
    }

    private function actionResult(Action $action, array $filters): array
    {
        return [
            'title' => $action->title,
            'type' => 'action',
            'project' => $action->project?->title ?? 'Non classee',
            'excerpt' => $this->excerpt([$action->description], $filters['q']),
            'date' => $action->updated_at,
            'url' => route('actions.show', $action),
        ];
    }

    private function fileResult(File $file, array $filters): array
    {
        return [
            'title' => $file->original_name,
            'type' => 'fichier',
            'project' => $file->project?->title ?? 'Sans projet',
            'excerpt' => $this->excerpt([$file->description, $file->path], $filters['q']),
            'date' => $file->updated_at,
            'url' => route('files.show', $file),
        ];
    }

    private function likeAny(Builder $query, array $columns, string $term): Builder
    {
        $pattern = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        foreach ($columns as $index => $column) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $query->{$method}($column, $operator, $pattern);
        }

        return $query;
    }

    private function excerpt(array $values, string $term): string
    {
        $text = collect($values)
            ->filter()
            ->first(fn ($value) => Str::contains(Str::lower($value), Str::lower($term)))
            ?? collect($values)->filter()->first()
            ?? '';

        return Str::limit(trim(preg_replace('/\s+/', ' ', $text)), 180);
    }

    private function statuses(): array
    {
        return array_unique([
            ...array_keys(MvpOptions::PROJECT_STATUSES),
            ...array_keys(MvpOptions::NOTE_STATUSES),
            ...array_keys(MvpOptions::DECISION_STATUSES),
            ...array_keys(MvpOptions::ACTION_STATUSES),
            ...MvpOptions::FILE_INDEXING_STATUSES,
        ]);
    }
}

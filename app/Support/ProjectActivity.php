<?php

namespace App\Support;

use App\Models\Action;
use App\Models\ActivityLog;
use App\Models\Decision;
use App\Models\File as StoredFile;
use App\Models\Note;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProjectActivity
{
    public const FILTERS = [
        'notes' => ['note_created'],
        'decisions' => ['decision_created'],
        'actions' => ['action_created', 'action_completed'],
        'files' => ['file_created'],
        'status' => ['project_status_changed'],
    ];

    private const TIMELINE_ACTIONS = [
        'project_created',
        'project_updated',
        'project_status_changed',
        'note_created',
        'decision_created',
        'action_created',
        'action_completed',
        'file_created',
    ];

    public static function log(Project $project, string $action, array $metadata = [], ?int $userId = null): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => Project::class,
            'entity_id' => $project->id,
            'metadata' => array_merge([
                'project_id' => $project->id,
                'project_title' => $project->title,
            ], $metadata),
        ]);
    }

    public static function logCreated(Model $model, string $action, ?int $userId = null): ?ActivityLog
    {
        $project = $model instanceof Project ? $model : $model->project;

        if (! $project instanceof Project) {
            return null;
        }

        return self::log($project, $action, self::metadataFor($model), $userId);
    }

    public static function logActionCompleted(Action $action, ?int $userId = null): ?ActivityLog
    {
        if (! $action->project instanceof Project) {
            return null;
        }

        return self::log($action->project, 'action_completed', array_merge(self::metadataFor($action), [
            'completed_at' => $action->completed_at?->toISOString(),
        ]), $userId);
    }

    public static function timeline(Project $project, ?string $filter = null): LengthAwarePaginator
    {
        $actions = self::FILTERS[$filter] ?? self::TIMELINE_ACTIONS;

        return ActivityLog::query()
            ->with('user')
            ->where('entity_type', Project::class)
            ->where('entity_id', $project->id)
            ->whereIn('action', $actions)
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (ActivityLog $log) => self::entry($log));
    }

    public static function filterOptions(): array
    {
        return [
            'all' => 'Tout',
            'notes' => 'Notes',
            'decisions' => 'Decisions',
            'actions' => 'Actions',
            'files' => 'Fichiers',
            'status' => 'Changements de statut',
        ];
    }

    public static function isValidFilter(?string $filter): bool
    {
        return $filter === null || array_key_exists($filter, self::FILTERS);
    }

    public static function changedImportantFields(Project $project): Collection
    {
        return collect($project->getChanges())
            ->except(['updated_at', 'slug', 'archived_at', 'status'])
            ->keys()
            ->values();
    }

    private static function metadataFor(Model $model): array
    {
        $base = [
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'title' => $model instanceof StoredFile ? $model->original_name : $model->getAttribute('title'),
        ];

        if ($model instanceof Note) {
            return array_merge($base, [
                'type' => $model->type,
                'status' => $model->status,
                'url' => route('notes.show', $model),
            ]);
        }

        if ($model instanceof Decision) {
            return array_merge($base, [
                'status' => $model->status,
                'url' => route('decisions.show', $model),
            ]);
        }

        if ($model instanceof Action) {
            return array_merge($base, [
                'status' => $model->status,
                'priority' => $model->priority,
                'url' => route('actions.show', $model),
            ]);
        }

        if ($model instanceof StoredFile) {
            return array_merge($base, [
                'extension' => $model->extension,
                'size' => $model->size,
                'url' => route('files.show', $model),
            ]);
        }

        if ($model instanceof Project) {
            return array_merge($base, [
                'status' => $model->status,
                'priority' => $model->priority,
                'url' => route('projects.show', $model),
            ]);
        }

        return $base;
    }

    private static function entry(ActivityLog $log): array
    {
        $metadata = $log->metadata ?? [];
        $title = (string) Arr::get($metadata, 'title', 'Sans titre');

        return match ($log->action) {
            'project_created' => self::formatEntry(
                $log,
                'Projet cree',
                'Projet',
                (string) Arr::get($metadata, 'project_title', $title),
                'Le projet a ete initialise.',
                'status',
            ),
            'project_status_changed' => self::formatEntry(
                $log,
                'Statut modifie',
                'Statut',
                self::statusChangeLabel($metadata),
                null,
                'status',
            ),
            'project_updated' => self::formatEntry(
                $log,
                'Projet modifie',
                'Modification',
                self::changesLabel($metadata),
                null,
                'status',
            ),
            'note_created' => self::formatEntry(
                $log,
                'Note ajoutee',
                'Note',
                $title,
                self::statusMetadata($metadata),
                'notes',
            ),
            'decision_created' => self::formatEntry(
                $log,
                'Decision creee',
                'Decision',
                $title,
                self::statusMetadata($metadata),
                'decisions',
            ),
            'action_created' => self::formatEntry(
                $log,
                'Action creee',
                'Action',
                $title,
                self::statusMetadata($metadata),
                'actions',
            ),
            'action_completed' => self::formatEntry(
                $log,
                'Action terminee',
                'Action',
                $title,
                'Marquee comme faite.',
                'actions',
            ),
            'file_created' => self::formatEntry(
                $log,
                'Fichier ajoute',
                'Fichier',
                $title,
                self::fileMetadata($metadata),
                'files',
            ),
            default => self::formatEntry($log, $log->action, 'Activite', $title, null, 'status'),
        };
    }

    private static function formatEntry(ActivityLog $log, string $label, string $badge, string $title, ?string $description, string $filter): array
    {
        return [
            'label' => $label,
            'badge' => $badge,
            'title' => $title,
            'description' => $description,
            'filter' => $filter,
            'url' => Arr::get($log->metadata ?? [], 'url'),
            'created_at' => $log->created_at,
            'user' => $log->user?->name,
        ];
    }

    private static function statusMetadata(array $metadata): ?string
    {
        return Arr::get($metadata, 'status') ? 'Statut : '.Arr::get($metadata, 'status') : null;
    }

    private static function fileMetadata(array $metadata): ?string
    {
        $parts = array_filter([
            Arr::get($metadata, 'extension') ? 'Format : '.Arr::get($metadata, 'extension') : null,
            Arr::get($metadata, 'size')
                ? 'Taille : '.number_format((int) Arr::get($metadata, 'size') / 1024, 1, ',', ' ').' Ko'
                : null,
        ]);

        return $parts ? implode(' - ', $parts) : null;
    }

    private static function statusChangeLabel(array $metadata): string
    {
        $from = Arr::get($metadata, 'old_status', 'non renseigne');
        $to = Arr::get($metadata, 'new_status', 'non renseigne');

        return "{$from} -> {$to}";
    }

    private static function changesLabel(array $metadata): string
    {
        $fields = Arr::get($metadata, 'fields', []);

        if (! is_array($fields) || $fields === []) {
            return 'Informations importantes mises a jour';
        }

        return 'Champs modifies : '.implode(', ', $fields);
    }
}

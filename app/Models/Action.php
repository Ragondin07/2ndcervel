<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Action extends Model
{
    use Searchable;

    protected $fillable = [
        'project_id',
        'note_id',
        'decision_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'archived_at',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(Decision::class);
    }

    public function searchableAs(): string
    {
        return 'actions';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'is_pinned' => $this->is_pinned,
            'type' => 'action',
            'project_id' => $this->project_id,
            'note_id' => $this->note_id,
            'decision_id' => $this->decision_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'archived_at' => $this->archived_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_timestamp' => $this->updated_at?->timestamp,
        ];
    }
}

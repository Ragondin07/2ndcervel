<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Note extends Model
{
    use Searchable;

    protected $fillable = [
        'project_id',
        'title',
        'content',
        'type',
        'status',
        'source_type',
        'source_detail',
        'archived_at',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class, 'source_note_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function searchableAs(): string
    {
        return 'notes';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'is_pinned' => $this->is_pinned,
            'type' => 'note',
            'project_id' => $this->project_id,
            'title' => $this->title,
            'content' => $this->content,
            'note_type' => $this->type,
            'status' => $this->status,
            'archived_at' => $this->archived_at?->toISOString(),
            'source_type' => $this->source_type,
            'source_detail' => $this->source_detail,
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_timestamp' => $this->updated_at?->timestamp,
        ];
    }
}

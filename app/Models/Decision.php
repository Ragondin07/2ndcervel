<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Decision extends Model
{
    use Searchable;

    protected $fillable = [
        'project_id',
        'source_note_id',
        'title',
        'decision',
        'justification',
        'alternatives',
        'risks',
        'impact',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sourceNote(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'source_note_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function searchableAs(): string
    {
        return 'decisions';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'type' => 'decision',
            'project_id' => $this->project_id,
            'source_note_id' => $this->source_note_id,
            'title' => $this->title,
            'decision' => $this->decision,
            'justification' => $this->justification,
            'alternatives' => $this->alternatives,
            'risks' => $this->risks,
            'impact' => $this->impact,
            'status' => $this->status,
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_timestamp' => $this->updated_at?->timestamp,
        ];
    }
}

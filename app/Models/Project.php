<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Project extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'status',
        'priority',
        'category',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class);
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
        return 'projects';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'type' => 'project',
            'project_id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'description' => $this->description,
            'status' => $this->status,
            'archived_at' => $this->archived_at?->toISOString(),
            'priority' => $this->priority,
            'category' => $this->category,
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_timestamp' => $this->updated_at?->timestamp,
        ];
    }
}

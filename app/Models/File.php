<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class File extends Model
{
    use Searchable;

    protected $fillable = [
        'project_id',
        'note_id',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'extension',
        'size',
        'hash',
        'description',
        'extracted_text',
        'ocr_text',
        'indexing_status',
        'extraction_status',
        'extraction_error',
        'ocr_status',
        'ocr_error',
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

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function searchableAs(): string
    {
        return 'files';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'is_pinned' => $this->is_pinned,
            'type' => 'file',
            'project_id' => $this->project_id,
            'note_id' => $this->note_id,
            'title' => $this->original_name,
            'original_name' => $this->original_name,
            'stored_name' => $this->stored_name,
            'path' => $this->path,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'description' => $this->description,
            'extracted_text' => $this->extracted_text,
            'ocr_text' => $this->ocr_text,
            'status' => $this->indexing_status,
            'extraction_status' => $this->extraction_status,
            'extraction_error' => $this->extraction_error,
            'ocr_status' => $this->ocr_status,
            'ocr_error' => $this->ocr_error,
            'archived_at' => $this->archived_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_timestamp' => $this->updated_at?->timestamp,
        ];
    }
}

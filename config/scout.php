<?php

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => env('SCOUT_QUEUE', false),
    'after_commit' => false,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://meilisearch:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            App\Models\Project::class => [
                'filterableAttributes' => ['type', 'project_id', 'status', 'archived_at', 'updated_at_timestamp'],
                'sortableAttributes' => ['updated_at_timestamp'],
            ],
            App\Models\Note::class => [
                'filterableAttributes' => ['type', 'project_id', 'status', 'archived_at', 'updated_at_timestamp'],
                'sortableAttributes' => ['updated_at_timestamp'],
            ],
            App\Models\Decision::class => [
                'filterableAttributes' => ['type', 'project_id', 'status', 'archived_at', 'updated_at_timestamp'],
                'sortableAttributes' => ['updated_at_timestamp'],
            ],
            App\Models\Action::class => [
                'filterableAttributes' => ['type', 'project_id', 'status', 'archived_at', 'updated_at_timestamp'],
                'sortableAttributes' => ['updated_at_timestamp'],
            ],
            App\Models\File::class => [
                'searchableAttributes' => ['title', 'original_name', 'stored_name', 'path', 'description', 'extracted_text', 'ocr_text'],
                'filterableAttributes' => ['type', 'project_id', 'status', 'archived_at', 'updated_at_timestamp'],
                'sortableAttributes' => ['updated_at_timestamp'],
            ],
        ],
    ],
];

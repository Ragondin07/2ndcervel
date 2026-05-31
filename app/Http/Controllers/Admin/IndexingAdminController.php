<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\IndexFileJob;
use App\Models\File;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IndexingAdminController extends Controller
{
    private const STATUS_LABELS = [
        'en_attente' => 'En attente',
        'en_cours' => 'En cours',
        'indexe' => 'Indexes',
        'erreur' => 'En erreur',
        'non_supporte' => 'Non supportes',
    ];

    public function index(): View
    {
        return view('admin.indexing', [
            'totalFiles' => File::query()->count(),
            'statusLabels' => self::STATUS_LABELS,
            'statusCounts' => [
                'en_attente' => $this->countFiles('en_attente'),
                'en_cours' => $this->countFiles('en_cours'),
                'indexe' => $this->countFiles('indexe'),
                'erreur' => $this->countFiles('erreur'),
                'non_supporte' => $this->countFiles('non_supporte'),
            ],
            'latestErrors' => File::query()
                ->with(['project', 'note'])
                ->where(function ($query): void {
                    $query->where('indexing_status', 'erreur')
                        ->orWhere('extraction_status', 'erreur');
                })
                ->latest('updated_at')
                ->limit(20)
                ->get(),
            'recentFiles' => File::query()
                ->with('project')
                ->latest('updated_at')
                ->limit(20)
                ->get(),
            'projects' => Project::query()
                ->orderBy('title')
                ->get(['id', 'title']),
        ]);
    }

    public function reindexFile(File $file): RedirectResponse
    {
        $this->queueFile($file);

        Log::info('Admin requested file reindex.', [
            'file_id' => $file->id,
            'user_id' => request()->user()?->id,
        ]);

        return back()->with('status', 'Indexation du fichier relancee.');
    }

    public function reindexProject(Project $project): RedirectResponse
    {
        $files = File::query()
            ->where('project_id', $project->id)
            ->get();

        $files->each(fn (File $file) => $this->queueFile($file));

        Log::info('Admin requested project file reindex.', [
            'project_id' => $project->id,
            'file_count' => $files->count(),
            'user_id' => request()->user()?->id,
        ]);

        return back()->with('status', "{$files->count()} fichier(s) relance(s) pour ce projet.");
    }

    public function reindexFiles(): RedirectResponse
    {
        $lock = Cache::lock('admin-reindex-files', 300);

        if (! $lock->get()) {
            return back()->with('status', 'Une reindexation de fichiers est deja en cours.');
        }

        try {
            $count = 0;

            File::query()
                ->select(['id'])
                ->chunkById(100, function ($files) use (&$count): void {
                    foreach ($files as $file) {
                        $this->queueFile($file);
                        $count++;
                    }
                });

            Log::info('Admin requested all files reindex.', [
                'file_count' => $count,
                'user_id' => request()->user()?->id,
            ]);

            return back()->with('status', "{$count} fichier(s) relance(s).");
        } finally {
            $lock->release();
        }
    }

    public function rebuildSearchIndex(): RedirectResponse
    {
        $lock = Cache::lock('admin-rebuild-search-index', 600);

        if (! $lock->get()) {
            return back()->with('status', 'Une reconstruction Meilisearch est deja en cours.');
        }

        try {
            Artisan::call('search:reindex');

            Log::warning('Admin rebuilt Meilisearch indexes.', [
                'user_id' => request()->user()?->id,
            ]);

            return back()->with('status', 'Index Meilisearch purge et reconstruit.');
        } finally {
            $lock->release();
        }
    }

    private function countFiles(string $status): int
    {
        return File::query()
            ->where(function ($query) use ($status): void {
                $query->where('indexing_status', $status)
                    ->orWhere('extraction_status', $status);
            })
            ->count();
    }

    private function queueFile(File $file): void
    {
        $file->update([
            'indexing_status' => 'en_attente',
            'extraction_status' => 'en_attente',
            'extraction_error' => null,
        ]);

        IndexFileJob::dispatch($file->id);
    }
}

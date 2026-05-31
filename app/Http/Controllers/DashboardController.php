<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Decision;
use App\Models\File;
use App\Models\Note;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'activeProjects' => Project::query()
                ->whereNull('archived_at')
                ->whereNotIn('status', ['archive', 'archivee', 'termine', 'abandonne'])
                ->latest('updated_at')
                ->limit(5)
                ->get(),
            'latestNotes' => Note::query()
                ->with('project')
                ->latest('updated_at')
                ->limit(5)
                ->get(),
            'latestDecisions' => Decision::query()
                ->with('project')
                ->latest('updated_at')
                ->limit(5)
                ->get(),
            'openActions' => Action::query()
                ->with('project')
                ->whereIn('status', ['a_faire', 'en_cours', 'bloquee'])
                ->latest('updated_at')
                ->limit(5)
                ->get(),
            'latestFiles' => File::query()
                ->with('project')
                ->latest('updated_at')
                ->limit(5)
                ->get(),
            'overdueActions' => Action::query()
                ->with('project')
                ->whereIn('status', ['a_faire', 'en_cours', 'bloquee'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->orderBy('due_date')
                ->limit(8)
                ->get(),
            'ocrErrors' => File::query()
                ->with('project')
                ->where('ocr_status', 'erreur')
                ->latest('updated_at')
                ->limit(8)
                ->get(),
            'unclassifiedFilesCount' => File::query()->whereNull('project_id')->whereNull('archived_at')->count(),
            'inboxItemsCount' => Note::query()->whereNull('project_id')->whereNull('archived_at')->count()
                + Decision::query()->whereNull('project_id')->whereNull('archived_at')->count()
                + Action::query()->whereNull('project_id')->whereNull('archived_at')->count()
                + File::query()->whereNull('project_id')->whereNull('archived_at')->count(),
            'pinnedProjects' => Project::query()->where('is_pinned', true)->latest('updated_at')->limit(5)->get(),
            'pinnedNotes' => Note::query()->with('project')->where('is_pinned', true)->latest('updated_at')->limit(5)->get(),
            'pinnedDecisions' => Decision::query()->with('project')->where('is_pinned', true)->latest('updated_at')->limit(5)->get(),
            'pinnedActions' => Action::query()->with('project')->where('is_pinned', true)->latest('updated_at')->limit(5)->get(),
            'pinnedFiles' => File::query()->with('project')->where('is_pinned', true)->latest('updated_at')->limit(5)->get(),
        ]);
    }
}

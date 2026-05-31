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
        ]);
    }
}

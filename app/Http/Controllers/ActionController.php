<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Support\MvpOptions;
use Illuminate\View\View;

class ActionController extends Controller
{
    public function index(): View
    {
        return view('actions.index', [
            'actions' => Action::query()
                ->with('project')
                ->latest('updated_at')
                ->get(),
            'statuses' => MvpOptions::ACTION_STATUSES,
        ]);
    }

    public function show(Action $action): View
    {
        $action->load(['project', 'note', 'decision']);

        return view('actions.show', [
            'action' => $action,
            'statuses' => MvpOptions::ACTION_STATUSES,
        ]);
    }
}

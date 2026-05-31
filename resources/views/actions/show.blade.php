<x-layouts.private :title="$action->title">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Description</h2>
                <div class="description-block">{{ $action->description ?: 'Aucune description.' }}</div>
            </div>
        </section>

        <section class="grid three">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Statut</h2>
                    <p><span class="badge">{{ $statuses[$action->status] ?? $action->status }}</span></p>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Projet</h2>
                    <p>
                        @if ($action->project)
                            <a href="{{ route('projects.show', $action->project) }}">{{ $action->project->title }}</a>
                        @else
                            Non classee
                        @endif
                    </p>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Priorite</h2>
                    <p>{{ $action->priority }}</p>
                </div>
            </div>
        </section>
    </div>
</x-layouts.private>

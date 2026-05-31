<x-layouts.private :title="$action->title">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <div>
                <h2>{{ $action->title }}</h2>
                <p class="item-meta">
                    <span class="badge">{{ $statuses[$action->status] ?? $action->status }}</span>
                    <span>{{ $action->project?->title ?? 'Sans projet' }}</span>
                    @if ($action->due_date)
                        <span>Échéance : {{ $action->due_date->format('d/m/Y') }}</span>
                    @endif
                </p>
            </div>
            <div class="row-actions">
                <form method="POST" action="{{ route('pin.toggle', ['type' => 'action', 'id' => $action->id]) }}">
                    @csrf
                    @method('PATCH')
                    <button class="secondary" type="submit">{{ $action->is_pinned ? '★ Favori' : '☆ Favori' }}</button>
                </form>
            </div>
        </div>

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

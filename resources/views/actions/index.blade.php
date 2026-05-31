<x-layouts.private title="Actions">
    <div class="grid">
        <div class="section-header">
            <h2>Actions</h2>
            <div class="row-actions"><a class="button secondary" href="{{ route('actions.index', ['pinned' => 1]) }}">Favoris</a><a class="button primary" href="{{ route('actions.create') }}">Nouvelle action</a></div>
        </div>

        <section class="panel">
            <div class="panel-inner">
                <form class="filters-form" method="GET" action="{{ route('actions.index') }}">
                    <div class="field">
                        <label for="project_id">Projet</label>
                        <select id="project_id" name="project_id">
                            <option value="">Tous</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) $filters['project_id'] === (string) $project->id)>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="">Tous</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="due">Échéance</label>
                        <select id="due" name="due">
                            <option value="">Toutes</option>
                            <option value="overdue" @selected($filters['due'] === 'overdue')>En retard</option>
                            <option value="today" @selected($filters['due'] === 'today')>Aujourd'hui</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="primary" type="submit">Filtrer</button>
                        <a class="button secondary" href="{{ route('actions.index') }}">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </section>

        <section class="kanban-board" aria-label="Kanban actions">
            @foreach (['a_faire', 'en_cours', 'bloquee', 'faite'] as $column)
                <div class="panel kanban-column">
                    <div class="panel-inner">
                        <h2 class="panel-title">{{ $statuses[$column] ?? $column }} <span class="badge badge-info">{{ ($kanban[$column] ?? collect())->count() }}</span></h2>
                        @forelse (($kanban[$column] ?? collect()) as $action)
                            <article class="kanban-card">
                                <a class="item-title" href="{{ route('actions.show', $action) }}">{{ $action->title }}</a>
                                <p class="item-meta">
                                    <span class="badge {{ $action->priority === 'haute' || $action->priority === 'critique' ? 'badge-warning' : '' }}">{{ $action->priority }}</span>
                                    @if ($action->due_date)
                                        <span @class(['badge', 'badge-danger' => $action->due_date->isPast() && $action->status !== 'faite'])>{{ $action->due_date->format('d/m/Y') }}</span>
                                    @endif
                                    <span>{{ $action->project?->title ?? 'Sans projet' }}</span>
                                </p>
                            </article>
                        @empty
                            <p class="empty">Aucune action.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </section>
    </div>
</x-layouts.private>

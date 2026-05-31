<x-layouts.private title="Decisions">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <h2>Toutes les decisions</h2>
            <div class="row-actions"><a class="button secondary" href="{{ route('decisions.index', ['pinned' => 1]) }}">Favoris</a><a class="button primary" href="{{ route('decisions.create') }}">Nouvelle decision</a></div>
        </div>

        <section class="panel">
            <div class="panel-inner">
                <form method="GET" action="{{ route('decisions.index') }}" class="filters-form">
                    <div class="field">
                        <label for="project_id">Projet</label>
                        <select id="project_id" name="project_id">
                            <option value="">Tous les projets</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) $filters['project_id'] === (string) $project->id)>
                                    {{ $project->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="">Tous les statuts</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit">Filtrer</button>
                        <a class="button secondary" href="{{ route('decisions.index') }}">Reinitialiser</a>
                    </div>
                </form>
            </div>
        </section>

        <section class="panel">
            <div class="panel-inner">
                @if ($decisions->isEmpty())
                    <p class="empty">Aucune decision pour ces filtres.</p>
                @else
                    <ul class="list">
                        @foreach ($decisions as $decision)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('decisions.show', $decision) }}">{{ $decision->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span>{{ $decision->created_at->format('d/m/Y') }}</span>
                                        <span>{{ $decision->project?->title ?? 'Non classee' }}</span>
                                        <span class="badge">{{ $statuses[$decision->status] ?? $decision->status }}</span>
                                    </p>
                                    <p class="item-meta">{{ \App\Http\Controllers\DecisionController::excerpt($decision->justification) }}</p>
                                </div>
                                <a class="button secondary" href="{{ route('decisions.edit', $decision) }}">Modifier</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.private>

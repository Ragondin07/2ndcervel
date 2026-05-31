<x-layouts.private :title="$project->title">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <h2>{{ $project->title }}</h2>
            <div class="row-actions">
                <form method="POST" action="{{ route('pin.toggle', ['type' => 'project', 'id' => $project->id]) }}">
                    @csrf
                    @method('PATCH')
                    <button class="secondary" type="submit">{{ $project->is_pinned ? '★ Favori' : '☆ Favori' }}</button>
                </form>
                <a class="button secondary" href="{{ route('projects.edit', $project) }}">Modifier</a>
                <form method="POST" action="{{ route('projects.archive', $project) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit">Archiver</button>
                </form>
            </div>
        </div>

        <section class="panel">
            <div class="panel-inner">
                <p class="item-meta">
                    <span class="badge">{{ $statuses[$project->status] ?? $project->status }}</span>
                    <span>Priorite: {{ $priorities[$project->priority] ?? $project->priority }}</span>
                    @if ($project->category)
                        <span>Categorie: {{ $project->category }}</span>
                    @endif
                    <span>Modifie le {{ $project->updated_at->format('d/m/Y H:i') }}</span>
                </p>

                @if ($project->summary)
                    <p>{{ $project->summary }}</p>
                @endif

                @if ($project->description)
                    <div class="description-block">{{ $project->description }}</div>
                @endif
            </div>
        </section>



        @php
            $totalActions = $project->actions->count();
            $doneActions = $project->actions->where('status', 'faite')->count();
            $progress = $totalActions > 0 ? (int) round(($doneActions / $totalActions) * 100) : 0;
        @endphp

        <section class="grid four">
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">Notes</p><p class="metric">{{ $project->notes->count() }}</p></div></div>
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">Décisions</p><p class="metric">{{ $project->decisions->count() }}</p></div></div>
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">Actions</p><p class="metric">{{ $doneActions }}/{{ $totalActions }}</p></div></div>
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">Fichiers</p><p class="metric">{{ $project->files->count() }}</p></div></div>
        </section>

        <section class="panel">
            <div class="panel-inner">
                <div class="mini-header">
                    <h2 class="panel-title">Progression des actions</h2>
                    <span class="badge badge-success">{{ $progress }}%</span>
                </div>
                <div class="progress"><span style="width: {{ $progress }}%"></span></div>
            </div>
        </section>

        <section class="panel" id="timeline">
            <div class="panel-inner">
                <div class="section-header">
                    <div>
                        <h2>Timeline du projet</h2>
                        <p class="item-meta">Historique chronologique des evenements importants, sans audit detaille.</p>
                    </div>
                </div>

                <div class="timeline-filters" aria-label="Filtres de timeline">
                    @foreach ($timelineFilters as $filterKey => $filterLabel)
                        @php
                            $url = $filterKey === 'all'
                                ? route('projects.show', $project)
                                : route('projects.show', ['project' => $project, 'timeline' => $filterKey]);
                            $active = $filterKey === 'all' ? $timelineFilter === null : $timelineFilter === $filterKey;
                        @endphp
                        <a @class(['badge', 'active' => $active]) href="{{ $url }}#timeline">{{ $filterLabel }}</a>
                    @endforeach
                </div>

                @if ($timeline->isEmpty())
                    <p class="empty">Aucun evenement important pour ce filtre.</p>
                @else
                    <ol class="timeline-list">
                        @foreach ($timeline as $entry)
                            <li class="timeline-item">
                                <time class="timeline-date" datetime="{{ $entry['created_at']->toISOString() }}">
                                    {{ $entry['created_at']->format('d/m/Y') }}<br>
                                    {{ $entry['created_at']->format('H:i') }}
                                </time>
                                <div class="timeline-content">
                                    <span class="badge">{{ $entry['badge'] }}</span>
                                    <p class="timeline-title">
                                        @if ($entry['url'])
                                            <a href="{{ $entry['url'] }}">{{ $entry['label'] }} - {{ $entry['title'] }}</a>
                                        @else
                                            {{ $entry['label'] }} - {{ $entry['title'] }}
                                        @endif
                                    </p>
                                    @if ($entry['description'])
                                        <p class="timeline-description">{{ $entry['description'] }}</p>
                                    @endif
                                    @if ($entry['user'])
                                        <p class="timeline-description">Par {{ $entry['user'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    @if ($timeline->hasPages())
                        <div class="pagination">{{ $timeline->links() }}</div>
                    @endif
                @endif
            </div>
        </section>

        <section class="grid two">
            @include('projects.partials.related-list', [
                'title' => 'Notes',
                'items' => $project->notes,
                'empty' => 'Aucune note rattachee.',
                'routeName' => 'notes.show',
                'meta' => fn ($note) => ($note->project?->title ?? 'Inbox').' - '.$note->updated_at->format('d/m/Y H:i'),
                'actionUrl' => route('notes.create', ['project_id' => $project->id, 'return_to' => route('projects.show', $project)]),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Decisions',
                'items' => $project->decisions,
                'empty' => 'Aucune decision rattachee.',
                'routeName' => 'decisions.show',
                'meta' => fn ($decision) => $decision->status.' - '.$decision->updated_at->format('d/m/Y H:i'),
                'actionUrl' => route('decisions.create', ['project_id' => $project->id, 'return_to' => route('projects.show', $project)]),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Actions',
                'items' => $project->actions,
                'empty' => 'Aucune action rattachee.',
                'routeName' => 'actions.show',
                'meta' => fn ($action) => $action->status.' - '.$action->updated_at->format('d/m/Y H:i'),
                'actionUrl' => route('actions.create', ['project_id' => $project->id, 'return_to' => route('projects.show', $project)]),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Fichiers',
                'items' => $project->files,
                'empty' => 'Aucun fichier rattache.',
                'routeName' => 'files.show',
                'titleField' => 'original_name',
                'meta' => fn ($file) => $file->indexing_status.' - '.$file->updated_at->format('d/m/Y H:i'),
                'actionUrl' => route('files.create', ['project_id' => $project->id, 'return_to' => route('projects.show', $project)]),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Études de prix',
                'items' => $project->notes->where('type', 'recherche'),
                'empty' => 'Aucune étude de prix.',
                'routeName' => 'notes.show',
                'meta' => fn ($note) => $note->updated_at->format('d/m/Y H:i'),
                'actionUrl' => route('notes.create', ['project_id' => $project->id, 'type' => 'recherche', 'title' => 'Étude de prix - ', 'return_to' => route('projects.show', $project)]),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Synthèses',
                'items' => $project->notes->where('type', 'synthese_ia'),
                'empty' => 'Aucune synthèse.',
                'routeName' => 'notes.show',
                'meta' => fn ($note) => $note->updated_at->format('d/m/Y H:i'),
                'actionUrl' => route('notes.create', ['project_id' => $project->id, 'type' => 'synthese_ia', 'return_to' => route('projects.show', $project)]),
            ])
        </section>
    </div>
</x-layouts.private>

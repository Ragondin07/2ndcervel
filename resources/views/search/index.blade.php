<x-layouts.private title="Recherche">
    <div class="grid">
        <section class="panel">
            <div class="panel-inner">
                <form class="search-form" method="GET" action="{{ route('search') }}">
                    <div class="field full">
                        <label for="q">Recherche</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Mot-cle a rechercher">
                    </div>

                    <div class="field">
                        <label for="project_id">Projet</label>
                        <select id="project_id" name="project_id">
                            <option value="">Tous les projets</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) $filters['project_id'] === (string) $project->id)>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            <option value="">Tous les types</option>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="">Tous les statuts</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button class="primary" type="submit">Rechercher</button>
                        <a class="button secondary" href="{{ route('search') }}">Reinitialiser</a>
                    </div>
                </form>
            </div>
        </section>

        @if ($engine)
            <div class="notice">Recherche executee avec {{ $engine === 'sql' ? 'SQL' : 'Meilisearch' }}.</div>
        @endif

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Resultats</h2>

                @if ($filters['q'] === '')
                    <p class="empty">Saisissez un terme pour lancer une recherche.</p>
                @elseif ($results->isEmpty())
                    <p class="empty">Aucun resultat trouve.</p>
                @else
                    <ul class="list">
                        @foreach ($results as $result)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ $result['url'] }}">{{ $result['title'] }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $result['type'] }}</span>
                                        <span>{{ $result['project'] }}</span>
                                        @if ($result['date'])
                                            <span>{{ $result['date']->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </p>
                                    @if ($result['excerpt'])
                                        <p class="empty">{!! preg_replace('/('.preg_quote($filters['q'], '/').')/iu', '<mark>$1</mark>', e($result['excerpt'])) !!}</p>
                                    @endif
                                </div>
                                <a class="button secondary" href="{{ $result['url'] }}">Ouvrir</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.private>

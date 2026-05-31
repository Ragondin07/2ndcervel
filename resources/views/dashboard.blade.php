<x-layouts.private title="Tableau de bord">
    <div class="grid">
        <form class="search-box" method="GET" action="{{ route('search') }}" aria-label="Recherche globale">
            <input name="q" type="search" placeholder="Rechercher dans les projets, notes, decisions, actions et fichiers">
            <button class="primary" type="submit">Rechercher</button>
        </form>

        <div class="section-header">
            <div>
                <h2>Vue d'ensemble</h2>
            </div>
            <a class="button primary" href="{{ route('quick-add') }}">Ajouter rapidement</a>
        </div>



        <section class="grid four">
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">Actions en retard</p><p class="metric">{{ $overdueActions->count() }}</p></div></div>
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">OCR en erreur</p><p class="metric">{{ $ocrErrors->count() }}</p></div></div>
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">Fichiers non classés</p><p class="metric">{{ $unclassifiedFilesCount }}</p></div></div>
            <div class="panel metric-card"><div class="panel-inner"><p class="item-meta">Inbox à traiter</p><p class="metric">{{ $inboxItemsCount }}</p></div></div>
        </section>

        <section class="grid two">
            <div class="panel card-accent">
                <div class="panel-inner">
                    <h2 class="panel-title">★ Éléments épinglés</h2>
                    @php
                        $pinned = collect()
                            ->merge($pinnedProjects->map(fn ($item) => ['label' => 'Projet', 'title' => $item->title, 'url' => route('projects.show', $item)]))
                            ->merge($pinnedNotes->map(fn ($item) => ['label' => 'Note', 'title' => $item->title, 'url' => route('notes.show', $item)]))
                            ->merge($pinnedDecisions->map(fn ($item) => ['label' => 'Décision', 'title' => $item->title, 'url' => route('decisions.show', $item)]))
                            ->merge($pinnedActions->map(fn ($item) => ['label' => 'Action', 'title' => $item->title, 'url' => route('actions.show', $item)]))
                            ->merge($pinnedFiles->map(fn ($item) => ['label' => 'Fichier', 'title' => $item->original_name, 'url' => route('files.show', $item)]));
                    @endphp
                    @if ($pinned->isEmpty())
                        <p class="empty">Aucun favori pour le moment.</p>
                    @else
                        <ul class="list compact-list">
                            @foreach ($pinned->take(10) as $item)
                                <li class="list-item"><span class="badge badge-info">{{ $item['label'] }}</span> <a class="item-title" href="{{ $item['url'] }}">{{ $item['title'] }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="panel card-accent">
                <div class="panel-inner">
                    <h2 class="panel-title">Ma journée</h2>
                    @if ($overdueActions->isEmpty() && $ocrErrors->isEmpty())
                        <p class="empty">Aucun blocage immédiat identifié.</p>
                    @else
                        <ul class="list compact-list">
                            @foreach ($overdueActions as $action)
                                <li class="list-item"><span class="badge badge-danger">Retard</span> <a href="{{ route('actions.show', $action) }}">{{ $action->title }}</a></li>
                            @endforeach
                            @foreach ($ocrErrors as $file)
                                <li class="list-item"><span class="badge badge-danger">OCR</span> <a href="{{ route('files.show', $file) }}">{{ $file->original_name }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid two">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Projets actifs</h2>
                    @if ($activeProjects->isEmpty())
                        <p class="empty">Aucun projet actif pour le moment.</p>
                    @else
                        <ul class="list">
                            @foreach ($activeProjects as $project)
                                <li class="list-item">
                                    <p class="item-title">
                                        <a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $project->status }}</span>
                                        @if ($project->category)
                                            <span>{{ $project->category }}</span>
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Actions ouvertes</h2>
                    @if ($openActions->isEmpty())
                        <p class="empty">Aucune action ouverte.</p>
                    @else
                        <ul class="list">
                            @foreach ($openActions as $action)
                                <li class="list-item">
                                    <p class="item-title">{{ $action->title }}</p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $action->status }}</span>
                                        @if ($action->project)
                                            <a href="{{ route('projects.show', $action->project) }}">{{ $action->project->title }}</a>
                                        @else
                                            Sans projet
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid three">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Dernieres notes</h2>
                    @if ($latestNotes->isEmpty())
                        <p class="empty">Aucune note recente.</p>
                    @else
                        <ul class="list">
                            @foreach ($latestNotes as $note)
                                <li class="list-item">
                                    <p class="item-title">
                                        <a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        @if ($note->project)
                                            <a href="{{ route('projects.show', $note->project) }}">{{ $note->project->title }}</a>
                                        @else
                                            Inbox
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Dernieres decisions</h2>
                    @if ($latestDecisions->isEmpty())
                        <p class="empty">Aucune decision recente.</p>
                    @else
                        <ul class="list">
                            @foreach ($latestDecisions as $decision)
                                <li class="list-item">
                                    <p class="item-title">
                                        <a href="{{ route('decisions.show', $decision) }}">{{ $decision->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $decision->status }}</span>
                                        @if ($decision->project)
                                            <a href="{{ route('projects.show', $decision->project) }}">{{ $decision->project->title }}</a>
                                        @else
                                            Sans projet
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Derniers fichiers</h2>
                    @if ($latestFiles->isEmpty())
                        <p class="empty">Aucun fichier recent.</p>
                    @else
                        <ul class="list">
                            @foreach ($latestFiles as $file)
                                <li class="list-item">
                                    <p class="item-title">
                                        <a href="{{ route('files.show', $file) }}">{{ $file->original_name }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $file->indexing_status }}</span>
                                        @if ($file->project)
                                            <a href="{{ route('projects.show', $file->project) }}">{{ $file->project->title }}</a>
                                        @else
                                            Sans projet
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>
    </div>
</x-layouts.private>

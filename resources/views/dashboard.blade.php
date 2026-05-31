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

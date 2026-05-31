<x-layouts.private title="Projets">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <h2>Projets actifs</h2>
            <div class="row-actions"><a class="button secondary" href="{{ route('projects.index', ['pinned' => 1]) }}">Favoris</a><a class="button primary" href="{{ route('projects.create') }}">Nouveau projet</a></div>
        </div>

        <section class="panel">
            <div class="panel-inner">
                @if ($activeProjects->isEmpty())
                    <p class="empty">Aucun projet actif.</p>
                @else
                    <ul class="list">
                        @foreach ($activeProjects as $project)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $statuses[$project->status] ?? $project->status }}</span>
                                        <span>{{ $project->priority }}</span>
                                        @if ($project->category)
                                            <span>{{ $project->category }}</span>
                                        @endif
                                        <span>Modifie le {{ $project->updated_at->format('d/m/Y H:i') }}</span>
                                    </p>
                                </div>
                                <div class="row-actions">
                                    <a class="button secondary" href="{{ route('projects.edit', $project) }}">Modifier</a>
                                    <form method="POST" action="{{ route('projects.archive', $project) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit">Archiver</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <div class="section-header">
            <h2>Projets archives</h2>
        </div>

        <section class="panel archived-panel">
            <div class="panel-inner">
                @if ($archivedProjects->isEmpty())
                    <p class="empty">Aucun projet archive.</p>
                @else
                    <ul class="list">
                        @foreach ($archivedProjects as $project)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">archive</span>
                                        <span>Archive le {{ $project->archived_at?->format('d/m/Y H:i') ?? 'date inconnue' }}</span>
                                    </p>
                                </div>
                                <a class="button secondary" href="{{ route('projects.edit', $project) }}">Modifier</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.private>

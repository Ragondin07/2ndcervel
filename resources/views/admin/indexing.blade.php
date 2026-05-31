<x-layouts.private title="Administration">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <section class="grid three">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Total fichiers</h2>
                    <p class="metric">{{ $totalFiles }}</p>
                </div>
            </div>
            @foreach ($statusCounts as $status => $count)
                <div class="panel">
                    <div class="panel-inner">
                        <h2 class="panel-title">{{ $statusLabels[$status] ?? $status }}</h2>
                        <p class="metric">{{ $count }}</p>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="panel">
            <div class="panel-inner">
                <div class="section-header">
                    <h2>Actions globales</h2>
                </div>
                <div class="row-actions">
                    <form method="POST" action="{{ route('admin.indexing.reindex-files') }}" onsubmit="return confirm('Relancer l indexation de tous les fichiers ?');">
                        @csrf
                        <button type="submit">Relancer tous les fichiers</button>
                    </form>

                    <form method="POST" action="{{ route('admin.indexing.rebuild-search') }}" onsubmit="return confirm('Purger et reconstruire tout l index Meilisearch ?');">
                        @csrf
                        <button class="danger" type="submit">Purger et reconstruire Meilisearch</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Relancer un projet</h2>
                @if ($projects->isEmpty())
                    <p class="empty">Aucun projet disponible.</p>
                @else
                    <ul class="list">
                        @foreach ($projects as $project)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">{{ $project->title }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.indexing.reindex-project', $project) }}" onsubmit="return confirm('Relancer l indexation des fichiers de ce projet ?');">
                                    @csrf
                                    <button type="submit">Relancer ce projet</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Dernieres erreurs</h2>
                @if ($latestErrors->isEmpty())
                    <p class="empty">Aucune erreur d'indexation recente.</p>
                @else
                    <ul class="list">
                        @foreach ($latestErrors as $file)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('files.show', $file) }}">{{ $file->original_name }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">indexation: {{ $file->indexing_status }}</span>
                                        <span class="badge">extraction: {{ $file->extraction_status }}</span>
                                        <span>{{ $file->project?->title ?? 'Sans projet' }}</span>
                                    </p>
                                    @if ($file->extraction_error)
                                        <p class="error">{{ $file->extraction_error }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.indexing.reindex-file', $file) }}">
                                    @csrf
                                    <button type="submit">Relancer</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Fichiers recents</h2>
                @if ($recentFiles->isEmpty())
                    <p class="empty">Aucun fichier.</p>
                @else
                    <ul class="list">
                        @foreach ($recentFiles as $file)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('files.show', $file) }}">{{ $file->original_name }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">indexation: {{ $file->indexing_status }}</span>
                                        <span class="badge">extraction: {{ $file->extraction_status }}</span>
                                        <span>{{ $file->updated_at->format('d/m/Y H:i') }}</span>
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('admin.indexing.reindex-file', $file) }}">
                                    @csrf
                                    <button type="submit">Relancer</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.private>

<x-layouts.private title="Fichiers">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <h2>Fichiers</h2>
            <div class="row-actions"><a class="button secondary" href="{{ route('files.index', ['pinned' => 1]) }}">Favoris</a><a class="button primary" href="{{ route('files.create') }}">Ajouter des fichiers</a></div>
        </div>

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Déplacement en masse</h2>
                <form id="bulk-file-project" class="inline-form" method="POST" action="{{ route('files.bulk-project') }}">
                    @csrf
                    @method('PATCH')
                    <div class="field">
                        <label for="bulk_project_id">Projet cible</label>
                        <input list="project_choices" id="bulk_project_search" class="project-search" placeholder="Rechercher un projet">
                        <select id="bulk_project_id" name="project_id">
                            <option value="">Retirer du projet / Inbox</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="primary" type="submit">Déplacer la sélection</button>
                </form>
                <datalist id="project_choices">
                    @foreach ($projects as $project)
                        <option data-id="{{ $project->id }}" value="{{ $project->title }}"></option>
                    @endforeach
                </datalist>
                <p class="item-meta">Cochez plusieurs fichiers ci-dessous, choisissez un projet, puis déplacez-les en une action.</p>
            </div>
        </section>

        <section class="panel">
            <div class="panel-inner">
                @if ($files->isEmpty())
                    <p class="empty">Aucun fichier pour le moment.</p>
                @else
                    <ul class="list">
                        @foreach ($files as $file)
                            <li class="list-item project-row card-accent">
                                <div>
                                    <label class="checkbox-line">
                                        <input form="bulk-file-project" type="checkbox" name="file_ids[]" value="{{ $file->id }}">
                                        <span class="item-title"><a href="{{ route('files.show', $file) }}">{{ $file->original_name }}</a></span>
                                    </label>
                                    <p class="item-meta">
                                        <span class="badge badge-info">{{ $file->extension ?: 'type inconnu' }}</span>
                                        <span class="badge {{ $file->ocr_status === 'erreur' ? 'badge-danger' : ($file->ocr_status === 'termine' ? 'badge-success' : 'badge-warning') }}">OCR: {{ $file->ocr_status }}</span>
                                        <span class="badge">indexation: {{ $file->indexing_status }}</span>
                                        <span>{{ $file->project?->title ?? 'Sans projet' }}</span>
                                        <span>{{ number_format(($file->size ?? 0) / 1024, 1, ',', ' ') }} Ko</span>
                                    </p>
                                </div>
                                <div class="row-actions">
                                    <form method="POST" action="{{ route('files.project', $file) }}" class="compact-project-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="project_id" aria-label="Changer de projet">
                                            <option value="">Sans projet</option>
                                            @foreach ($projects as $project)
                                                <option value="{{ $project->id }}" @selected($file->project_id === $project->id)>{{ $project->title }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit">Changer</button>
                                    </form>
                                    <form method="POST" action="{{ route('files.project', $file) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="project_id" value="">
                                        <button class="secondary" type="submit">Retirer du projet</button>
                                    </form>
                                    <a class="button secondary" href="{{ route('files.download', $file) }}">Télécharger</a>
                                    <form method="POST" action="{{ route('files.ocr', $file) }}">
                                        @csrf
                                        <button type="submit">Relancer OCR</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>

    <script>
        document.getElementById('bulk_project_search')?.addEventListener('change', (event) => {
            const option = [...document.querySelectorAll('#project_choices option')].find((item) => item.value === event.target.value);
            if (option?.dataset.id) {
                document.getElementById('bulk_project_id').value = option.dataset.id;
            }
        });
    </script>
</x-layouts.private>

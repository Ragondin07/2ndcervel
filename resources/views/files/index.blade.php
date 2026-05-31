<x-layouts.private title="Fichiers">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <h2>Fichiers</h2>
            <a class="button primary" href="{{ route('files.create') }}">Ajouter des fichiers</a>
        </div>

        <section class="panel">
            <div class="panel-inner">
                @if ($files->isEmpty())
                    <p class="empty">Aucun fichier pour le moment.</p>
                @else
                    <ul class="list">
                        @foreach ($files as $file)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('files.show', $file) }}">{{ $file->original_name }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">indexation: {{ $file->indexing_status }}</span>
                                        <span class="badge">extraction: {{ $file->extraction_status }}</span>
                                        <span class="badge">OCR: {{ $file->ocr_status }}</span>
                                        <span>{{ $file->project?->title ?? 'Sans projet' }}</span>
                                        <span>{{ $file->extension ?: 'extension inconnue' }}</span>
                                        <span>{{ number_format(($file->size ?? 0) / 1024, 1, ',', ' ') }} Ko</span>
                                    </p>
                                </div>
                                <div class="row-actions">
                                    <a class="button secondary" href="{{ route('files.download', $file) }}">Telecharger</a>
                                    <form method="POST" action="{{ route('files.reindex', $file) }}">
                                        @csrf
                                        <button type="submit">Relancer l'indexation</button>
                                    </form>
                                    <form method="POST" action="{{ route('files.ocr', $file) }}">
                                        @csrf
                                        <button type="submit">Relancer l'OCR</button>
                                    </form>
                                    <form method="POST" action="{{ route('files.destroy', $file) }}" onsubmit="return confirm('Supprimer ce fichier ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit">Supprimer</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.private>

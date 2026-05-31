<x-layouts.private :title="$file->original_name">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <div>
                <h2>{{ $file->original_name }}</h2>
                <p class="item-meta">Ajoute le {{ $file->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="row-actions">
                <a class="button primary" href="{{ route('files.download', $file) }}">Telecharger</a>
                <form method="POST" action="{{ route('files.reindex', $file) }}">
                    @csrf
                    <button type="submit">Relancer l'indexation</button>
                </form>
                <form method="POST" action="{{ route('files.destroy', $file) }}" onsubmit="return confirm('Supprimer ce fichier ?');">
                    @csrf
                    @method('DELETE')
                    <button class="danger" type="submit">Supprimer</button>
                </form>
            </div>
        </div>

        <section class="grid three">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Projet</h2>
                    <p>
                        @if ($file->project)
                            <a href="{{ route('projects.show', $file->project) }}">{{ $file->project->title }}</a>
                        @else
                            Sans projet
                        @endif
                    </p>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Note</h2>
                    <p>
                        @if ($file->note)
                            <a href="{{ route('notes.show', $file->note) }}">{{ $file->note->title }}</a>
                        @else
                            Aucune note
                        @endif
                    </p>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Indexation</h2>
                    <p><span class="badge">{{ $file->indexing_status }}</span></p>
                    <p class="item-meta">Extraction : {{ $file->extraction_status }}</p>
                    <p class="item-meta">OCR : {{ $file->ocr_status }}</p>
                </div>
            </div>
        </section>

        @if ($file->extraction_error)
            <section class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Erreur d'extraction</h2>
                    <p class="error">{{ $file->extraction_error }}</p>
                </div>
            </section>
        @endif

        <section class="grid two">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Metadonnees</h2>
                    <ul class="meta-list">
                        <li><strong>Nom stocke :</strong> {{ $file->stored_name }}</li>
                        <li><strong>Chemin :</strong> {{ $file->path }}</li>
                        <li><strong>MIME :</strong> {{ $file->mime_type ?: 'inconnu' }}</li>
                        <li><strong>Extension :</strong> {{ $file->extension ?: 'inconnue' }}</li>
                        <li><strong>Taille :</strong> {{ number_format(($file->size ?? 0) / 1024, 1, ',', ' ') }} Ko</li>
                        <li><strong>Hash SHA-256 :</strong> <code>{{ $file->hash ?: 'non calcule' }}</code></li>
                    </ul>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Description</h2>
                    <div class="description-block">{{ $file->description ?: 'Aucune description.' }}</div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Texte extrait</h2>
                <div class="description-block">{{ $file->extracted_text ?: 'Aucun texte extrait pour le moment.' }}</div>
            </div>
        </section>
    </div>
</x-layouts.private>

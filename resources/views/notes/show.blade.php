<x-layouts.private :title="$note->title">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <div>
                <h2>{{ $note->title }}</h2>
                <p class="item-meta">
                    <span class="badge badge-info">{{ $types[$note->type] ?? $note->type }}</span>
                    <span class="badge">{{ $statuses[$note->status] ?? $note->status }}</span>
                    {{ $note->project?->title ?? 'Inbox' }}
                </p>
            </div>
            <div class="row-actions">
                <form method="POST" action="{{ route('pin.toggle', ['type' => 'note', 'id' => $note->id]) }}">
                    @csrf
                    @method('PATCH')
                    <button class="secondary" type="submit">{{ $note->is_pinned ? '★ Favori' : '☆ Favori' }}</button>
                </form>
                <a class="button secondary" href="{{ route('notes.edit', $note) }}">Modifier</a>
                @if ($note->status !== 'archivee')
                    <form method="POST" action="{{ route('notes.archive', $note) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit">Archiver</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Supprimer cette note ?');">
                    @csrf
                    @method('DELETE')
                    <button class="danger" type="submit">Supprimer</button>
                </form>
            </div>
        </div>

        <div class="context-layout">
            <main class="grid">
                <section class="panel">
                    <div class="panel-inner">
                        <h2 class="panel-title">Contenu</h2>
                        <div class="description-block">{{ $note->content }}</div>
                    </div>
                </section>

                <section class="grid two">
                    <div class="panel">
                        <div class="panel-inner">
                            <h2 class="panel-title">Source</h2>
                            <p>{{ $note->source_type ?: 'Non renseignee' }}</p>
                            @if ($note->source_detail)
                                <p class="item-meta">{{ $note->source_detail }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-inner">
                            <h2 class="panel-title">Modification</h2>
                            <p>{{ $note->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="context-panel panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Contexte</h2>
                    <h3>Projet associé</h3>
                    <p>@if ($note->project)<a href="{{ route('projects.show', $note->project) }}">{{ $note->project->title }}</a>@else Inbox @endif</p>

                    <h3>Décisions liées</h3>
                    @forelse ($note->decisions as $decision)
                        <p><a href="{{ route('decisions.show', $decision) }}">{{ $decision->title }}</a></p>
                    @empty <p class="empty">Aucune.</p> @endforelse

                    <h3>Actions liées</h3>
                    @forelse ($note->actions as $action)
                        <p><a href="{{ route('actions.show', $action) }}">{{ $action->title }}</a></p>
                    @empty <p class="empty">Aucune.</p> @endforelse

                    <h3>Fichiers liés</h3>
                    @forelse ($note->files as $file)
                        <p><a href="{{ route('files.show', $file) }}">{{ $file->original_name }}</a></p>
                    @empty <p class="empty">Aucun.</p> @endforelse

                    <h3>Notes du même projet</h3>
                    @forelse ($relatedNotes as $relatedNote)
                        <p><a href="{{ route('notes.show', $relatedNote) }}">{{ $relatedNote->title }}</a></p>
                    @empty <p class="empty">Aucune.</p> @endforelse
                </div>
            </aside>
        </div>
    </div>
</x-layouts.private>

<x-layouts.private :title="$note->title">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <div>
                <h2>{{ $note->title }}</h2>
                <p class="item-meta">
                    {{ $types[$note->type] ?? $note->type }} -
                    {{ $statuses[$note->status] ?? $note->status }} -
                    {{ $note->project?->title ?? 'Inbox' }}
                </p>
            </div>
            <div class="row-actions">
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

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Contenu</h2>
                <div class="description-block">{{ $note->content }}</div>
            </div>
        </section>

        <section class="grid three">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Projet</h2>
                    <p>
                        @if ($note->project)
                            <a href="{{ route('projects.show', $note->project) }}">{{ $note->project->title }}</a>
                        @else
                            Inbox
                        @endif
                    </p>
                </div>
            </div>
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
    </div>
</x-layouts.private>

<x-layouts.private title="Inbox">
    <div class="grid">
        <div class="section-header">
            <h2>Notes sans projet</h2>
            <a class="button primary" href="{{ route('notes.create') }}">Nouvelle note</a>
        </div>

        <section class="panel">
            <div class="panel-inner">
                @if ($notes->isEmpty())
                    <p class="empty">Aucune note dans l'Inbox.</p>
                @else
                    <ul class="list">
                        @foreach ($notes as $note)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $types[$note->type] ?? $note->type }}</span>
                                        <span>Modifiee le {{ $note->updated_at->format('d/m/Y H:i') }}</span>
                                    </p>
                                </div>
                                <a class="button secondary" href="{{ route('notes.edit', $note) }}">Classer / modifier</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.private>

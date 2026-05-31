<x-layouts.private title="Notes">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <h2>Notes actives</h2>
            <a class="button primary" href="{{ route('notes.create') }}">Nouvelle note</a>
        </div>

        <section class="panel">
            <div class="panel-inner">
                @if ($activeNotes->isEmpty())
                    <p class="empty">Aucune note active.</p>
                @else
                    <ul class="list">
                        @foreach ($activeNotes as $note)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">{{ $types[$note->type] ?? $note->type }}</span>
                                        <span>{{ $statuses[$note->status] ?? $note->status }}</span>
                                        <span>{{ $note->project?->title ?? 'Inbox' }}</span>
                                        <span>Modifiee le {{ $note->updated_at->format('d/m/Y H:i') }}</span>
                                    </p>
                                </div>
                                <div class="row-actions">
                                    <a class="button secondary" href="{{ route('notes.edit', $note) }}">Modifier</a>
                                    <form method="POST" action="{{ route('notes.archive', $note) }}">
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
            <h2>Notes archivees</h2>
        </div>

        <section class="panel archived-panel">
            <div class="panel-inner">
                @if ($archivedNotes->isEmpty())
                    <p class="empty">Aucune note archivee.</p>
                @else
                    <ul class="list">
                        @foreach ($archivedNotes as $note)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title">
                                        <a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a>
                                    </p>
                                    <p class="item-meta">
                                        <span class="badge">archivee</span>
                                        <span>{{ $note->project?->title ?? 'Inbox' }}</span>
                                    </p>
                                </div>
                                <a class="button secondary" href="{{ route('notes.edit', $note) }}">Modifier</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.private>

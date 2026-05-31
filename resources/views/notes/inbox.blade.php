<x-layouts.private title="Inbox">
    <div class="grid">
        <div class="section-header">
            <div>
                <h2>Boite d'entree</h2>
                <p class="item-meta">{{ $counts['total'] }} element(s) sans projet a trier rapidement.</p>
            </div>
            <a class="button primary" href="{{ route('quick-add') }}">Ajout rapide</a>
        </div>

        <section class="grid four inbox-metrics" aria-label="Resume de l Inbox">
            <div class="panel"><div class="panel-inner"><p class="metric">{{ $counts['notes'] }}</p><p class="item-meta">notes sans projet</p></div></div>
            <div class="panel"><div class="panel-inner"><p class="metric">{{ $counts['decisions'] }}</p><p class="item-meta">decisions sans projet</p></div></div>
            <div class="panel"><div class="panel-inner"><p class="metric">{{ $counts['actions'] }}</p><p class="item-meta">actions sans projet</p></div></div>
            <div class="panel"><div class="panel-inner"><p class="metric">{{ $counts['files'] }}</p><p class="item-meta">fichiers sans projet</p></div></div>
        </section>

        @if ($counts['total'] === 0)
            <section class="panel">
                <div class="panel-inner">
                    <p class="empty">Inbox vide : aucun contenu non classe.</p>
                </div>
            </section>
        @else
            <section class="panel">
                <div class="panel-inner">
                    <h3 class="panel-title">Recemment ajoutes sans classement</h3>
                    <ul class="list compact-list">
                        @foreach ($recentItems as $item)
                            <li class="list-item project-row">
                                <div>
                                    <p class="item-title"><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></p>
                                    <p class="item-meta"><span class="badge">{{ $item['label'] }}</span> Ajoute/modifie le {{ $item['updated_at']->format('d/m/Y H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="panel">
                <div class="panel-inner">
                    <h3 class="panel-title">Notes a trier</h3>
                    @if ($notes->isEmpty())
                        <p class="empty">Aucune note sans projet.</p>
                    @else
                        <ul class="list inbox-list">
                            @foreach ($notes as $note)
                                <li class="list-item inbox-item">
                                    <div class="inbox-main">
                                        <p class="item-title"><a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a></p>
                                        <p class="item-meta"><span class="badge">{{ $types[$note->type] ?? $note->type }}</span> Modifiee le {{ $note->updated_at->format('d/m/Y H:i') }}</p>
                                        <p class="item-excerpt">{{ Str::limit($note->content, 180) }}</p>
                                    </div>
                                    <div class="inbox-actions">
                                        @include('notes.partials.inbox-project-form', ['type' => 'note', 'id' => $note->id, 'projects' => $projects])

                                        <form class="inline-form" method="post" action="{{ route('inbox.notes.type', $note) }}">
                                            @csrf
                                            @method('patch')
                                            <label class="sr-only" for="note-type-{{ $note->id }}">Type</label>
                                            <select id="note-type-{{ $note->id }}" name="type">
                                                @foreach ($types as $value => $label)
                                                    <option value="{{ $value }}" @selected($note->type === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="secondary" type="submit">Changer type</button>
                                        </form>

                                        <div class="row-actions tight-actions">
                                            <form method="post" action="{{ route('inbox.notes.convert-decision', $note) }}">
                                                @csrf
                                                <button class="secondary" type="submit">En decision</button>
                                            </form>
                                            <form method="post" action="{{ route('inbox.notes.convert-action', $note) }}">
                                                @csrf
                                                <button class="secondary" type="submit">En action</button>
                                            </form>
                                            @include('notes.partials.inbox-item-actions', ['type' => 'note', 'id' => $note->id])
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="panel">
                <div class="panel-inner">
                    <h3 class="panel-title">Decisions sans projet</h3>
                    @include('notes.partials.inbox-simple-list', ['items' => $decisions, 'itemType' => 'decision', 'empty' => 'Aucune decision sans projet.', 'projects' => $projects])
                </div>
            </section>

            <section class="panel">
                <div class="panel-inner">
                    <h3 class="panel-title">Actions sans projet</h3>
                    @include('notes.partials.inbox-simple-list', ['items' => $actions, 'itemType' => 'action', 'empty' => 'Aucune action sans projet.', 'projects' => $projects])
                </div>
            </section>

            <section class="panel">
                <div class="panel-inner">
                    <h3 class="panel-title">Fichiers sans projet</h3>
                    @include('notes.partials.inbox-simple-list', ['items' => $files, 'itemType' => 'file', 'empty' => 'Aucun fichier sans projet.', 'projects' => $projects])
                </div>
            </section>
        @endif
    </div>
</x-layouts.private>

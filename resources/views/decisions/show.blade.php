<x-layouts.private :title="$decision->title">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <div>
                <h2>{{ $decision->title }}</h2>
                <p class="item-meta">Modifiee le {{ $decision->updated_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="row-actions">
                <a class="button secondary" href="{{ route('decisions.edit', $decision) }}">Modifier</a>
                <form method="POST" action="{{ route('decisions.destroy', $decision) }}" onsubmit="return confirm('Supprimer cette decision ?');">
                    @csrf
                    @method('DELETE')
                    <button class="danger" type="submit">Supprimer</button>
                </form>
            </div>
        </div>

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Decision</h2>
                <div class="description-block">{{ $decision->decision }}</div>
            </div>
        </section>

        <section class="grid two">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Statut</h2>
                    <p><span class="badge">{{ $statuses[$decision->status] ?? $decision->status }}</span></p>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Projet</h2>
                    <p>
                        @if ($decision->project)
                            <a href="{{ route('projects.show', $decision->project) }}">{{ $decision->project->title }}</a>
                        @else
                            Non classee
                        @endif
                    </p>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Note source</h2>
                    <p>
                        @if ($decision->sourceNote)
                            <a href="{{ route('notes.show', $decision->sourceNote) }}">{{ $decision->sourceNote->title }}</a>
                        @else
                            Non renseignee
                        @endif
                    </p>
                </div>
            </div>
        </section>

        <section class="grid two">
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Justification</h2>
                    <div class="description-block">{{ $decision->justification ?: 'Non renseignee.' }}</div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Alternatives</h2>
                    <div class="description-block">{{ $decision->alternatives ?: 'Non renseignees.' }}</div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Risques</h2>
                    <div class="description-block">{{ $decision->risks ?: 'Non renseignes.' }}</div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-inner">
                    <h2 class="panel-title">Impact</h2>
                    <div class="description-block">{{ $decision->impact ?: 'Non renseigne.' }}</div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.private>

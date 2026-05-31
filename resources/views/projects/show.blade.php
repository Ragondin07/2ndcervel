<x-layouts.private :title="$project->title">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="section-header">
            <h2>{{ $project->title }}</h2>
            <div class="row-actions">
                <a class="button secondary" href="{{ route('projects.edit', $project) }}">Modifier</a>
                <form method="POST" action="{{ route('projects.archive', $project) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit">Archiver</button>
                </form>
            </div>
        </div>

        <section class="panel">
            <div class="panel-inner">
                <p class="item-meta">
                    <span class="badge">{{ $statuses[$project->status] ?? $project->status }}</span>
                    <span>Priorite: {{ $priorities[$project->priority] ?? $project->priority }}</span>
                    @if ($project->category)
                        <span>Categorie: {{ $project->category }}</span>
                    @endif
                    <span>Modifie le {{ $project->updated_at->format('d/m/Y H:i') }}</span>
                </p>

                @if ($project->summary)
                    <p>{{ $project->summary }}</p>
                @endif

                @if ($project->description)
                    <div class="description-block">{{ $project->description }}</div>
                @endif
            </div>
        </section>

        <section class="grid two">
            @include('projects.partials.related-list', [
                'title' => 'Notes',
                'items' => $project->notes,
                'empty' => 'Aucune note rattachee.',
                'routeName' => 'notes.show',
                'meta' => fn ($note) => ($note->project?->title ?? 'Inbox').' - '.$note->updated_at->format('d/m/Y H:i'),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Decisions',
                'items' => $project->decisions,
                'empty' => 'Aucune decision rattachee.',
                'routeName' => 'decisions.show',
                'meta' => fn ($decision) => $decision->status.' - '.$decision->updated_at->format('d/m/Y H:i'),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Actions',
                'items' => $project->actions,
                'empty' => 'Aucune action rattachee.',
                'routeName' => 'actions.show',
                'meta' => fn ($action) => $action->status.' - '.$action->updated_at->format('d/m/Y H:i'),
            ])

            @include('projects.partials.related-list', [
                'title' => 'Fichiers',
                'items' => $project->files,
                'empty' => 'Aucun fichier rattache.',
                'routeName' => 'files.show',
                'titleField' => 'original_name',
                'meta' => fn ($file) => $file->indexing_status.' - '.$file->updated_at->format('d/m/Y H:i'),
            ])
        </section>
    </div>
</x-layouts.private>

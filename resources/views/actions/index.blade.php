<x-layouts.private title="Actions">
    <section class="panel">
        <div class="panel-inner">
            @if ($actions->isEmpty())
                <p class="empty">Aucune action pour le moment.</p>
            @else
                <ul class="list">
                    @foreach ($actions as $action)
                        <li class="list-item project-row">
                            <div>
                                <p class="item-title">
                                    <a href="{{ route('actions.show', $action) }}">{{ $action->title }}</a>
                                </p>
                                <p class="item-meta">
                                    <span class="badge">{{ $statuses[$action->status] ?? $action->status }}</span>
                                    <span>{{ $action->project?->title ?? 'Non classee' }}</span>
                                    <span>{{ $action->updated_at->format('d/m/Y H:i') }}</span>
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
</x-layouts.private>

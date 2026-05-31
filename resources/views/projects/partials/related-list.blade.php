@php
    $titleField = $titleField ?? 'title';
    $routeName = $routeName ?? null;
    $actionUrl = $actionUrl ?? null;
    $actionLabel = $actionLabel ?? '+';
@endphp

<div class="panel card-accent">
    <div class="panel-inner">
        <div class="mini-header">
            <h2 class="panel-title">{{ $title }} <span class="badge badge-info">{{ $items->count() }}</span></h2>
            @if ($actionUrl)
                <a class="quick-add-button" href="{{ $actionUrl }}" aria-label="Ajouter {{ $title }}">{{ $actionLabel }}</a>
            @endif
        </div>
        @if ($items->isEmpty())
            <p class="empty">{{ $empty }}</p>
        @else
            <ul class="list">
                @foreach ($items as $item)
                    <li class="list-item">
                        <p class="item-title">
                            @if ($routeName)
                                <a href="{{ route($routeName, $item) }}">{{ $item->{$titleField} }}</a>
                            @else
                                {{ $item->{$titleField} }}
                            @endif
                        </p>
                        <p class="item-meta">{{ $meta($item) }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

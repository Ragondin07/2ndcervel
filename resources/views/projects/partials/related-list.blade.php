@php
    $titleField = $titleField ?? 'title';
    $routeName = $routeName ?? null;
@endphp

<div class="panel">
    <div class="panel-inner">
        <h2 class="panel-title">{{ $title }}</h2>
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

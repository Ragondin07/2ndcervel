@if ($items->isEmpty())
    <p class="empty">{{ $empty }}</p>
@else
    <ul class="list inbox-list">
        @foreach ($items as $item)
            @php
                $title = $itemType === 'file' ? $item->original_name : $item->title;
                $url = match ($itemType) {
                    'decision' => route('decisions.show', $item),
                    'action' => route('actions.show', $item),
                    'file' => route('files.show', $item),
                };
                $meta = match ($itemType) {
                    'decision' => 'Decision - '.$item->status,
                    'action' => 'Action - '.$item->status,
                    'file' => 'Fichier - '.($item->extension ?: 'sans extension'),
                };
                $excerpt = match ($itemType) {
                    'decision' => $item->decision,
                    'action' => $item->description,
                    'file' => $item->description,
                };
            @endphp
            <li class="list-item inbox-item">
                <div class="inbox-main">
                    <p class="item-title"><a href="{{ $url }}">{{ $title }}</a></p>
                    <p class="item-meta"><span class="badge">{{ $meta }}</span> Modifie le {{ $item->updated_at->format('d/m/Y H:i') }}</p>
                    @if ($excerpt)
                        <p class="item-excerpt">{{ Str::limit($excerpt, 180) }}</p>
                    @endif
                </div>
                <div class="inbox-actions">
                    @include('notes.partials.inbox-project-form', ['type' => $itemType, 'id' => $item->id, 'projects' => $projects])
                    <div class="row-actions tight-actions">
                        @include('notes.partials.inbox-item-actions', ['type' => $itemType, 'id' => $item->id])
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif

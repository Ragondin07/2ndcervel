<div class="form-grid">
    <div class="field full">
        <label for="title">Titre</label>
        <input id="title" name="title" type="text" value="{{ old('title', $note->title) }}" required>
        @error('title')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="content">Contenu</label>
        <textarea id="content" name="content" rows="10" required>{{ old('content', $note->content) }}</textarea>
        @error('content')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="project_id">Projet</label>
        <select id="project_id" name="project_id">
            <option value="">Aucun projet - Inbox</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((string) old('project_id', $note->project_id) === (string) $project->id)>
                    {{ $project->title }}
                </option>
            @endforeach
        </select>
        @error('project_id')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="type">Type</label>
        <select id="type" name="type" required>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $note->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="status">Statut</label>
        <select id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $note->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="source_type">Source</label>
        <input id="source_type" name="source_type" type="text" value="{{ old('source_type', $note->source_type) }}">
        @error('source_type')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="source_detail">Detail de source</label>
        <textarea id="source_detail" name="source_detail" rows="3">{{ old('source_detail', $note->source_detail) }}</textarea>
        @error('source_detail')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-actions">
    <button class="primary" type="submit">{{ $submitLabel }}</button>
    <a class="button secondary" href="{{ $note->exists ? route('notes.show', $note) : route('notes.index') }}">Annuler</a>
</div>

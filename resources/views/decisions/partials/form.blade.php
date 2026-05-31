<input type="hidden" name="return_to" value="{{ old('return_to', $returnTo ?? '') }}">
<div class="form-grid">
    <div class="field">
        <label for="project_id">Projet</label>
        <select id="project_id" name="project_id">
            <option value="">Aucun projet</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((string) old('project_id', $decision->project_id) === (string) $project->id)>
                    {{ $project->title }}
                </option>
            @endforeach
        </select>
        @error('project_id')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="source_note_id">Note source</label>
        <select id="source_note_id" name="source_note_id">
            <option value="">Aucune note source</option>
            @foreach ($notes as $note)
                <option value="{{ $note->id }}" @selected((string) old('source_note_id', $decision->source_note_id) === (string) $note->id)>
                    {{ $note->title }}{{ $note->project ? ' - '.$note->project->title : ' - Inbox' }}
                </option>
            @endforeach
        </select>
        @error('source_note_id')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="title">Titre</label>
        <input id="title" name="title" type="text" value="{{ old('title', $decision->title) }}" required>
        @error('title')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="decision">Decision</label>
        <textarea id="decision" name="decision" rows="5" required>{{ old('decision', $decision->decision) }}</textarea>
        @error('decision')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="justification">Justification</label>
        <textarea id="justification" name="justification" rows="5">{{ old('justification', $decision->justification) }}</textarea>
        @error('justification')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="status">Statut</label>
        <select id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $decision->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="alternatives">Alternatives</label>
        <textarea id="alternatives" name="alternatives" rows="4">{{ old('alternatives', $decision->alternatives) }}</textarea>
        @error('alternatives')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="risks">Risques</label>
        <textarea id="risks" name="risks" rows="4">{{ old('risks', $decision->risks) }}</textarea>
        @error('risks')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="impact">Impact</label>
        <textarea id="impact" name="impact" rows="4">{{ old('impact', $decision->impact) }}</textarea>
        @error('impact')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-actions">
    <button class="primary" type="submit">{{ $submitLabel }}</button>
    <a class="button secondary" href="{{ ($returnTo ?? null) ?: ($decision->exists ? route('decisions.show', $decision) : route('decisions.index')) }}">Annuler</a>
</div>

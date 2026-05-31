<input type="hidden" name="return_to" value="{{ old('return_to', $returnTo ?? '') }}">
<div class="form-grid">
    <div class="field full">
        <label for="title">Titre</label>
        <input id="title" name="title" type="text" value="{{ old('title', $action->title) }}" required>
        @error('title')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="project_id">Projet</label>
        <select id="project_id" name="project_id">
            <option value="">Aucun projet</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((string) old('project_id', $action->project_id) === (string) $project->id)>{{ $project->title }}</option>
            @endforeach
        </select>
        @error('project_id')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="status">Statut</label>
        <select id="status" name="status">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $action->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="priority">Priorite</label>
        <select id="priority" name="priority">
            @foreach ($priorities as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $action->priority) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('priority')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="due_date">Echeance</label>
        <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $action->due_date?->toDateString()) }}">
        @error('due_date')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field full">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6">{{ old('description', $action->description) }}</textarea>
        @error('description')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-actions">
    <button class="primary" type="submit">Enregistrer</button>
    <a class="button secondary" href="{{ $returnTo ?: route('actions.index') }}">Annuler</a>
</div>

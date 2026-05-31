<div class="form-grid">
    <div class="field full">
        <label for="title">Titre</label>
        <input id="title" name="title" type="text" value="{{ old('title', $project->title) }}" required>
        @error('title')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="summary">Resume</label>
        <textarea id="summary" name="summary" rows="3">{{ old('summary', $project->summary) }}</textarea>
        @error('summary')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6">{{ old('description', $project->description) }}</textarea>
        @error('description')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="status">Statut</label>
        <select id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="priority">Priorite</label>
        <select id="priority" name="priority" required>
            @foreach ($priorities as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $project->priority) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('priority')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field full">
        <label for="category">Categorie</label>
        <input id="category" name="category" type="text" value="{{ old('category', $project->category) }}">
        @error('category')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-actions">
    <button class="primary" type="submit">{{ $submitLabel }}</button>
    <a class="button secondary" href="{{ $project->exists ? route('projects.show', $project) : route('projects.index') }}">Annuler</a>
</div>

<x-layouts.private title="Ajout rapide">
    <div class="grid">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <section class="panel">
            <div class="panel-inner">
                <h2 class="panel-title">Creer rapidement une note, une decision ou une action</h2>

                <form method="POST" action="{{ route('quick-add.store') }}" class="stack-form">
                    @csrf

                    <div class="form-grid">
                        <div class="field">
                            <label for="content_type">Type de contenu</label>
                            <select id="content_type" name="content_type" required>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" @selected(old('content_type', 'note') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('content_type')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="project_id">Projet</label>
                            <select id="project_id" name="project_id">
                                <option value="">Aucun projet</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected((string) old('project_id') === (string) $project->id)>{{ $project->title }}</option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field full">
                            <label for="title">Titre</label>
                            <input id="title" name="title" type="text" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field full">
                            <label for="content">Contenu</label>
                            <textarea id="content" name="content" rows="8" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="status">Statut</label>
                            <select id="status" name="status" required>
                                <optgroup label="Notes">
                                    @foreach ($noteStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', 'brouillon') === $value)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Decisions">
                                    @foreach ($decisionStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Actions">
                                    @foreach ($actionStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('status')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="primary" type="submit">Creer</button>
                        <a class="button secondary" href="{{ route('dashboard') }}">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-layouts.private>

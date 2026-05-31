<x-layouts.private title="Ajouter des fichiers">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data" class="stack-form">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label for="project_id">Projet</label>
                        <select id="project_id" name="project_id">
                            <option value="">Aucun projet</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) old('project_id') === (string) $project->id)>
                                    {{ $project->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="note_id">Note liee</label>
                        <select id="note_id" name="note_id">
                            <option value="">Aucune note</option>
                            @foreach ($notes as $note)
                                <option value="{{ $note->id }}" @selected((string) old('note_id') === (string) $note->id)>
                                    {{ $note->title }}{{ $note->project ? ' - '.$note->project->title : ' - Inbox' }}
                                </option>
                            @endforeach
                        </select>
                        @error('note_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field full">
                        <label for="uploads">Fichiers</label>
                        <input id="uploads" class="file-input" name="uploads[]" type="file" multiple required>
                        <p class="item-meta">Taille maximale par fichier : {{ $maxUploadSizeMb }} Mo.</p>
                        @error('uploads')
                            <div class="error">{{ $message }}</div>
                        @enderror
                        @error('uploads.*')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button class="primary" type="submit">Uploader</button>
                    <a class="button secondary" href="{{ route('files.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </section>
</x-layouts.private>

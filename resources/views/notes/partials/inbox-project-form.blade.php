<form class="inline-form" method="post" action="{{ route('inbox.assign-project', ['type' => $type, 'id' => $id]) }}">
    @csrf
    @method('patch')
    <label class="sr-only" for="project-{{ $type }}-{{ $id }}">Projet</label>
    <select id="project-{{ $type }}-{{ $id }}" name="project_id" required>
        <option value="">Choisir un projet</option>
        @foreach ($projects as $project)
            <option value="{{ $project->id }}">{{ $project->title }}</option>
        @endforeach
    </select>
    <button class="primary" type="submit">Classer</button>
</form>

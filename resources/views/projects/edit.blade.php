<x-layouts.private title="Modifier le projet">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('projects.update', $project) }}" class="stack-form">
                @csrf
                @method('PUT')
                @include('projects.partials.form', ['submitLabel' => 'Enregistrer'])
            </form>
        </div>
    </section>
</x-layouts.private>

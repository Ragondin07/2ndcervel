<x-layouts.private title="Modifier la note">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('notes.update', $note) }}" class="stack-form">
                @csrf
                @method('PUT')
                @include('notes.partials.form', ['submitLabel' => 'Enregistrer'])
            </form>
        </div>
    </section>
</x-layouts.private>

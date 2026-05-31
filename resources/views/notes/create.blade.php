<x-layouts.private title="Nouvelle note">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('notes.store') }}" class="stack-form">
                @csrf
                @include('notes.partials.form', ['submitLabel' => 'Creer la note'])
            </form>
        </div>
    </section>
</x-layouts.private>

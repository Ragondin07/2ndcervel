<x-layouts.private title="Modifier la decision">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('decisions.update', $decision) }}" class="stack-form">
                @csrf
                @method('PUT')
                @include('decisions.partials.form', ['submitLabel' => 'Enregistrer'])
            </form>
        </div>
    </section>
</x-layouts.private>

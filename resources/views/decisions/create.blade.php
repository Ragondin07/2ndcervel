<x-layouts.private title="Nouvelle decision">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('decisions.store') }}" class="stack-form">
                @csrf
                @include('decisions.partials.form', ['submitLabel' => 'Creer la decision'])
            </form>
        </div>
    </section>
</x-layouts.private>

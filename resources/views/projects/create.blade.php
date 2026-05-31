<x-layouts.private title="Nouveau projet">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('projects.store') }}" class="stack-form">
                @csrf
                @include('projects.partials.form', ['submitLabel' => 'Creer le projet'])
            </form>
        </div>
    </section>
</x-layouts.private>

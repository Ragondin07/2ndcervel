<x-layouts.private title="Nouvelle action">
    <section class="panel">
        <div class="panel-inner">
            <form method="POST" action="{{ route('actions.store') }}" class="stack-form">
                @csrf
                @include('actions.partials.form')
            </form>
        </div>
    </section>
</x-layouts.private>

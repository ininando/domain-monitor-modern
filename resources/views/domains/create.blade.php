<x-layouts.app title="Tambah Domain">
    <div class="mx-auto max-w-4xl">
        <x-page-header
            title="Tambah domain"
            description="Catat domain baru milik OPD untuk dipantau masa berlakunya."
            :back="route('domains.index')"
        />

        @include('domains.partials.form', [
            'action' => route('domains.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan domain',
            'batal' => route('domains.index'),
        ])
    </div>
</x-layouts.app>

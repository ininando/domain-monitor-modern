<x-layouts.app title="Tambah OPD">
    <div class="mx-auto max-w-3xl">
        <x-page-header
            title="Tambah OPD"
            description="Daftarkan organisasi perangkat daerah baru."
            :back="route('opd.index')"
        />

        @include('opd.partials.form', [
            'action' => route('opd.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan OPD',
        ])
    </div>
</x-layouts.app>

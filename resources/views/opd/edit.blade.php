<x-layouts.app title="Edit OPD">
    <div class="mx-auto max-w-3xl">
        <x-page-header
            title="Edit OPD"
            :description="$opd->nama_opd"
            :back="route('opd.index')"
        />

        @include('opd.partials.form', [
            'action' => route('opd.update', $opd),
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
        ])
    </div>
</x-layouts.app>

<x-layouts.app title="Edit Domain">
    <div class="mx-auto max-w-4xl">
        <x-page-header
            title="Edit domain"
            :description="$domain->nama_domain"
            :back="route('domains.show', $domain)"
        />

        @include('domains.partials.form', [
            'action' => route('domains.update', $domain),
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
            'batal' => route('domains.show', $domain),
        ])
    </div>
</x-layouts.app>

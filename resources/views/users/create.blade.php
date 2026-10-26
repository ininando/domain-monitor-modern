<x-layouts.app title="Tambah User">
    <div class="mx-auto max-w-3xl">
        <x-page-header
            title="Tambah user"
            description="Buat akun baru untuk pegawai Diskominfo."
            :back="route('users.index')"
        />

        @include('users.partials.form', [
            'action' => route('users.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan user',
        ])
    </div>
</x-layouts.app>

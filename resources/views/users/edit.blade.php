<x-layouts.app title="Edit User">
    <div class="mx-auto max-w-3xl">
        <x-page-header
            title="Edit user"
            :description="$user->email"
            :back="route('users.index')"
        />

        @include('users.partials.form', [
            'action' => route('users.update', $user),
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
        ])
    </div>
</x-layouts.app>

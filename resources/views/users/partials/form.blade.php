{{-- Form user bersama untuk tambah dan edit. Variabel: $user, $action, $method, $submitLabel --}}

@php
    use App\Enums\UserRole;

    $opsiPeran = collect(UserRole::cases())->mapWithKeys(fn (UserRole $peran) => [$peran->value => $peran->label()])->all();
    $sedangEdit = $user->exists;
    $akunSendiri = $sedangEdit && $user->is(auth()->user());
@endphp

<form method="POST" action="{{ $action }}" x-data="{ mengirim: false, lihat: false }" @submit="mengirim = true" class="card" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6 p-6 sm:p-8">
        <div>
            <h2 class="text-base font-semibold text-ink">Data akun</h2>
            <p class="mt-1 text-sm text-ink-muted">Kolom bertanda <span class="text-red-600 dark:text-red-400">*</span> wajib diisi.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <x-input name="name" label="Nama lengkap" :value="$user->name" required maxlength="100" autocomplete="off" placeholder="Nama pegawai" />
            <x-input name="email" label="Email" type="email" :value="$user->email" required autocomplete="off" placeholder="nama@tomohonkota.go.id" />
        </div>

        <div>
            <x-select
                name="role"
                label="Peran"
                :options="$opsiPeran"
                :value="$user->role?->value"
                required
                :hint="$akunSendiri ? 'Anda tidak dapat menurunkan peran akun Anda sendiri.' : 'Super Admin dapat mengelola user; Admin tidak.'"
            />
        </div>

        <div class="border-t border-line pt-6">
            <h2 class="text-base font-semibold text-ink">{{ $sedangEdit ? 'Ganti password' : 'Password' }}</h2>
            <p class="mt-1 text-sm text-ink-muted">
                {{ $sedangEdit ? 'Kosongkan jika password tidak ingin diubah.' : 'Minimal 8 karakter.' }}
            </p>

            <div class="mt-5 grid gap-6 sm:grid-cols-2">
                <x-input name="password" label="Password" type="password" :required="! $sedangEdit" autocomplete="new-password" x-bind:type="lihat ? 'text' : 'password'" />
                <x-input name="password_confirmation" label="Ulangi password" type="password" :required="! $sedangEdit" autocomplete="new-password" x-bind:type="lihat ? 'text' : 'password'" />
            </div>

            <label class="mt-4 inline-flex cursor-pointer items-center gap-2.5 text-sm text-ink-muted select-none">
                <input type="checkbox" x-model="lihat" class="focus-ring size-4 rounded border-line-strong accent-primary">
                Tampilkan password
            </label>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-line px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
        <x-button :href="route('users.index')" variant="secondary">Batal</x-button>
        <x-button icon="save" loading="mengirim">{{ $submitLabel }}</x-button>
    </div>
</form>

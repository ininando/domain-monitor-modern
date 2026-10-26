@php
    use App\Enums\UserRole;
@endphp

<x-layouts.app title="Manajemen User">
    <x-page-header
        title="Manajemen user"
        description="Kelola akun pegawai Diskominfo yang dapat mengakses sistem."
    >
        <x-slot:actions>
            <x-button :href="route('users.create')" icon="plus">Tambah user</x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('users.index') }}" role="search" class="mb-5 flex items-center gap-2" @submit="$dispatch('memuat')">
        <div class="relative min-w-0 flex-1 sm:max-w-sm">
            <label for="cari-user" class="sr-only">Cari user</label>
            <x-lucide-search class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-ink-subtle" aria-hidden="true" />
            <input id="cari-user" type="search" name="q" value="{{ $kataKunci }}" placeholder="Nama atau email" class="field pl-10">
        </div>
        <x-button variant="secondary">Cari</x-button>
        @if ($kataKunci !== '')
            <x-button :href="route('users.index')" variant="ghost" icon="x">Reset</x-button>
        @endif
    </form>

    @if ($daftarUser->isEmpty())
        <div class="card">
            <x-empty-state icon="search-x" title="Tidak ada user yang cocok" description="Coba kata kunci lain.">
                <x-button :href="route('users.index')" variant="secondary">Hapus pencarian</x-button>
            </x-empty-state>
        </div>
    @else
        <x-table caption="Daftar user" :skeleton-cols="4">
            <x-slot:head>
                <th scope="col" class="px-5 py-3.5">Nama</th>
                <th scope="col" class="px-5 py-3.5">Peran</th>
                <th scope="col" class="hidden px-5 py-3.5 md:table-cell">Dibuat</th>
                <th scope="col" class="w-px px-3 py-3.5 text-right sm:px-5"><span class="sr-only">Aksi</span></th>
            </x-slot:head>

            @foreach ($daftarUser as $user)
                @php $akunSendiri = $user->is(auth()->user()); @endphp
                <tr class="transition-colors duration-150 hover:bg-surface-muted/50">
                    <td class="w-full max-w-0 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-bold text-on-primary-soft" aria-hidden="true">{{ $user->initials() }}</span>
                            <div class="min-w-0">
                                <p class="font-semibold text-ink">
                                    {{ $user->name }}
                                    @if ($akunSendiri)
                                        <span class="ml-1 rounded-full bg-surface-muted px-2 py-0.5 text-[11px] font-semibold text-ink-muted">Anda</span>
                                    @endif
                                </p>
                                <p class="truncate text-xs text-ink-muted">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <x-badge-status :level="$user->role === UserRole::SuperAdmin ? 'info' : 'neutral'">{{ $user->role->label() }}</x-badge-status>
                    </td>
                    <td class="hidden px-5 py-4 whitespace-nowrap text-ink-muted tabular-nums md:table-cell">{{ $user->created_at?->translatedFormat('d M Y') }}</td>
                    <td class="px-3 py-4 sm:px-5">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('users.edit', $user) }}" class="focus-ring inline-flex size-9 items-center justify-center rounded-lg pointer-coarse:size-11 text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink" aria-label="Edit {{ $user->name }}" title="Edit">
                                <x-lucide-pencil class="size-4" aria-hidden="true" />
                            </a>
                            @if ($akunSendiri)
                                <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg text-ink-subtle/50" title="Akun Anda sendiri tidak dapat dihapus" aria-label="Akun Anda sendiri tidak dapat dihapus" role="img">
                                    <x-lucide-trash-2 class="size-4" aria-hidden="true" />
                                </span>
                            @else
                                <x-delete-button
                                    :action="route('users.destroy', $user)"
                                    :label="'Hapus '.$user->name"
                                    title="Hapus akun?"
                                    :message="'Akun '.$user->name.' ('.$user->email.') akan dihapus dan tidak dapat login lagi.'"
                                />
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach

            @if ($daftarUser->hasPages())
                <x-slot:footer>
                    {{ $daftarUser->links() }}
                </x-slot:footer>
            @endif
        </x-table>
    @endif
</x-layouts.app>

@props([
    'title' => null,
])

@php
    $user = auth()->user();

    /** @var list<array{label: string, route: string, icon: string, active: string, superAdmin?: bool}> $menu */
    $menu = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'active' => 'dashboard'],
        ['label' => 'OPD', 'route' => 'opd.index', 'icon' => 'building-2', 'active' => 'opd.*'],
        ['label' => 'Domain', 'route' => 'domains.index', 'icon' => 'globe', 'active' => 'domains.*'],
        ['label' => 'Pemetaan', 'route' => 'pemetaan.index', 'icon' => 'chart-pie', 'active' => 'pemetaan.*'],
        ['label' => 'Laporan', 'route' => 'laporan.index', 'icon' => 'file-text', 'active' => 'laporan.*'],
        ['label' => 'Manajemen User', 'route' => 'users.index', 'icon' => 'users', 'active' => 'users.*', 'superAdmin' => true],
    ];

    $menu = array_filter($menu, fn (array $item): bool => ! ($item['superAdmin'] ?? false) || $user === null || $user->isSuperAdmin());

    $searchAction = Route::has('domains.index') ? route('domains.index') : route('opd.index');
    $searchLabel = Route::has('domains.index') ? 'Cari domain' : 'Cari OPD';
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo-tomohon-128.png') }}" type="image/png">
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans">
    <a href="#konten" class="focus-ring sr-only z-[60] rounded-control bg-primary px-4 py-2 text-sm font-semibold text-on-primary focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
        Lewati ke konten utama
    </a>

    <div x-data="appShell" @keydown.escape.window="drawerOpen = false" class="min-h-full">
        {{-- Overlay drawer (mobile/tablet) --}}
        <div
            x-show="drawerOpen"
            x-cloak
            x-transition.opacity.duration.200ms
            @click="drawerOpen = false"
            class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-[2px] lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Sidebar --}}
        <aside
            id="sidebar"
            :class="drawerOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-line bg-surface transition-[transform,width] duration-200 ease-out lg:translate-x-0 lg:sidebar-collapsed:w-[72px]"
            aria-label="Navigasi utama"
        >
            <div class="flex h-16 shrink-0 items-center gap-3 px-4 lg:sidebar-collapsed:justify-center lg:sidebar-collapsed:px-0">
                <img src="{{ asset('images/logo-tomohon-128.png') }}" alt="" class="size-10 shrink-0 object-contain" width="40" height="40">
                <div class="min-w-0 leading-tight lg:sidebar-collapsed:hidden">
                    <p class="truncate text-sm font-bold text-ink">Monitor Domain</p>
                    <p class="truncate text-xs text-ink-muted">Diskominfo Kota Tomohon</p>
                </div>
                <button type="button" @click="drawerOpen = false" class="focus-ring ml-auto inline-flex size-9 items-center justify-center rounded-control text-ink-muted hover:bg-surface-muted hover:text-ink lg:hidden" aria-label="Tutup menu">
                    <x-lucide-x class="size-5" aria-hidden="true" />
                </button>
            </div>

            <nav class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto px-3 py-4 [scrollbar-width:thin] [scrollbar-color:var(--line-strong)_transparent]">
                <p class="mb-2 px-3 text-[11px] font-semibold tracking-wider text-ink-subtle uppercase lg:sidebar-collapsed:sr-only">Menu</p>
                <ul class="space-y-1" role="list">
                    @foreach ($menu as $item)
                        <li>
                            <x-nav-link
                                :href="Route::has($item['route']) ? route($item['route']) : null"
                                :active="request()->routeIs($item['active'])"
                                :icon="$item['icon']"
                            >{{ $item['label'] }}</x-nav-link>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="hidden shrink-0 border-t border-line p-3 lg:block">
                <button
                    type="button"
                    @click="toggleCollapse()"
                    class="focus-ring group flex w-full items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink lg:sidebar-collapsed:justify-center lg:sidebar-collapsed:px-0"
                    :aria-label="collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                    :aria-expanded="(!collapsed).toString()"
                    aria-controls="sidebar"
                >
                    <x-lucide-panel-left-close class="size-5 shrink-0 lg:sidebar-collapsed:hidden" aria-hidden="true" />
                    <x-lucide-panel-left-open class="hidden size-5 shrink-0 lg:sidebar-collapsed:block" aria-hidden="true" />
                    <span class="lg:sidebar-collapsed:sr-only">Ciutkan sidebar</span>
                </button>
            </div>
        </aside>

        {{-- Area utama --}}
        <div class="flex min-h-dvh flex-col transition-[padding] duration-200 ease-out lg:pl-64 lg:sidebar-collapsed:pl-[72px]">
            <header class="sticky top-0 z-30 border-b border-line bg-canvas/85 backdrop-blur supports-[backdrop-filter]:bg-canvas/70">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" @click="drawerOpen = true" class="focus-ring -ml-1 inline-flex size-10 items-center justify-center rounded-control text-ink-muted hover:bg-surface-muted hover:text-ink lg:hidden" aria-label="Buka menu" aria-controls="sidebar" :aria-expanded="drawerOpen.toString()">
                        <x-lucide-menu class="size-5" aria-hidden="true" />
                    </button>

                    <form
                        method="GET"
                        action="{{ $searchAction }}"
                        role="search"
                        class="relative w-full max-w-md"
                        x-data
                        @keydown.slash.window="if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.cari.focus() }"
                    >
                        <label for="pencarian-global" class="sr-only">{{ $searchLabel }}</label>
                        <x-lucide-search class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-ink-subtle" aria-hidden="true" />
                        <input
                            x-ref="cari"
                            id="pencarian-global"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="{{ $searchLabel }}…"
                            class="field h-10 rounded-full bg-surface pr-12 pl-10"
                        >
                        <kbd class="pointer-events-none absolute top-1/2 right-3 hidden -translate-y-1/2 rounded-md border border-line px-1.5 py-0.5 font-sans text-[11px] text-ink-subtle sm:block">/</kbd>
                    </form>

                    <div class="ml-auto flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="$store.theme.toggle()"
                            class="focus-ring inline-flex size-10 items-center justify-center rounded-full text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink"
                            aria-label="Ganti tema terang/gelap"
                        >
                            <x-lucide-moon class="size-5 dark:hidden" aria-hidden="true" />
                            <x-lucide-sun class="hidden size-5 dark:block" aria-hidden="true" />
                        </button>

                        @isset($notifikasi)
                            <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false; $refs.lonceng.focus()" class="relative">
                                <button
                                    x-ref="lonceng"
                                    type="button"
                                    @click="open = ! open"
                                    class="focus-ring relative inline-flex size-10 items-center justify-center rounded-full text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink"
                                    :aria-expanded="open.toString()"
                                    aria-controls="panel-notifikasi"
                                    aria-label="Notifikasi: {{ $notifikasi['jumlah'] }} domain kritis"
                                >
                                    <x-lucide-bell class="size-5" aria-hidden="true" />
                                    @if ($notifikasi['jumlah'] > 0)
                                        <span class="absolute top-1.5 right-1.5 inline-flex min-w-4.5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] leading-4.5 font-bold text-white ring-2 ring-canvas tabular-nums dark:bg-red-500" aria-hidden="true">{{ $notifikasi['jumlah'] > 9 ? '9+' : $notifikasi['jumlah'] }}</span>
                                    @endif
                                </button>

                                <div
                                    id="panel-notifikasi"
                                    x-show="open"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="absolute right-0 mt-2 w-[min(22rem,calc(100vw-2rem))] origin-top-right overflow-hidden rounded-xl border border-line bg-surface shadow-pop"
                                >
                                    <div class="flex items-center justify-between border-b border-line px-4 py-3">
                                        <p class="text-sm font-semibold text-ink">Perlu tindakan segera</p>
                                        <x-badge-status level="critical">{{ $notifikasi['jumlah'] }} domain</x-badge-status>
                                    </div>
                                    @forelse ($notifikasi['daftar'] as $domainKritis)
                                        <a href="{{ route('domains.show', $domainKritis) }}" class="focus-ring flex items-center justify-between gap-3 px-4 py-3 transition-colors duration-150 hover:bg-surface-muted">
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-medium text-ink">{{ $domainKritis->nama_domain }}</span>
                                                <span class="block truncate text-xs text-ink-muted">{{ $domainKritis->opd->nama_opd }}</span>
                                            </span>
                                            <span class="shrink-0 text-xs font-semibold text-red-700 tabular-nums dark:text-red-300">{{ $domainKritis->label_expired }}</span>
                                        </a>
                                    @empty
                                        <div class="flex items-center gap-3 px-4 py-6 text-sm text-ink-muted">
                                            <x-lucide-circle-check-big class="size-5 text-emerald-600 dark:text-emerald-400" aria-hidden="true" />
                                            Tidak ada domain kritis saat ini.
                                        </div>
                                    @endforelse
                                    @if ($notifikasi['jumlah'] > 0)
                                        <a href="{{ route('domains.index', ['level' => 'critical']) }}" class="focus-ring flex items-center justify-center gap-1.5 border-t border-line px-4 py-2.5 text-sm font-semibold text-primary hover:bg-surface-muted">
                                            Lihat semua domain kritis
                                            <x-lucide-arrow-right class="size-4" aria-hidden="true" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endisset

                        <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false; $refs.pemicu.focus()" class="relative">
                            <button
                                x-ref="pemicu"
                                type="button"
                                @click="open = ! open"
                                class="focus-ring flex items-center gap-2.5 rounded-full py-1 pr-2 pl-1 transition-colors duration-150 hover:bg-surface-muted"
                                :aria-expanded="open.toString()"
                                aria-haspopup="menu"
                                aria-controls="menu-profil"
                            >
                                <span class="inline-flex size-8 items-center justify-center rounded-full bg-primary-soft text-xs font-bold text-on-primary-soft">
                                    {{ $user?->initials() ?? 'T' }}
                                </span>
                                <span class="hidden text-left leading-tight md:block">
                                    <span class="block text-sm font-semibold text-ink">{{ $user?->name ?? 'Tamu' }}</span>
                                    <span class="block text-xs text-ink-muted">{{ $user?->role->label() ?? 'Belum login' }}</span>
                                </span>
                                <x-lucide-chevron-down class="hidden size-4 text-ink-subtle md:block" aria-hidden="true" />
                            </button>

                            <div
                                id="menu-profil"
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute right-0 mt-2 w-60 origin-top-right rounded-xl border border-line bg-surface p-1.5 shadow-pop"
                                role="menu"
                            >
                                <div class="border-b border-line px-3 py-2.5">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $user?->name ?? 'Tamu' }}</p>
                                    <p class="truncate text-xs text-ink-muted">{{ $user?->email ?? 'Login tersedia di Fase 2' }}</p>
                                </div>
                                @if ($user && Route::has('logout'))
                                    <form method="POST" action="{{ route('logout') }}" class="pt-1.5">
                                        @csrf
                                        <button type="submit" role="menuitem" class="focus-ring flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10">
                                            <x-lucide-log-out class="size-4" aria-hidden="true" />
                                            Keluar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main id="konten" tabindex="-1" class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 outline-none sm:px-6 lg:px-8">
                {{ $slot }}
            </main>

            <footer class="mx-auto w-full max-w-7xl px-4 pb-6 text-xs text-ink-subtle sm:px-6 lg:px-8">
                &copy; {{ now()->year }} Dinas Komunikasi dan Informatika Kota Tomohon
            </footer>
        </div>
    </div>

    <x-toast />
    <x-modal-confirm />
</body>
</html>

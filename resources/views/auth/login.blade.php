<x-layouts.guest title="Masuk">
    <div class="flex min-h-dvh">
        {{-- Panel branding (desktop) --}}
        <aside class="relative hidden w-[46%] max-w-2xl flex-col justify-between overflow-hidden bg-sky-950 p-12 text-white lg:flex xl:p-16" aria-label="Tentang aplikasi">
            {{-- Pola grid dekoratif --}}
            <div class="pointer-events-none absolute inset-0 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:40px_40px]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -top-40 -right-40 size-[28rem] rounded-full bg-sky-500/25 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-48 -left-24 size-[26rem] rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex items-center gap-3">
                <img src="{{ asset('images/logo-tomohon-128.png') }}" alt="" class="size-12 shrink-0 object-contain" width="48" height="48">
                <div class="leading-tight">
                    <p class="text-sm font-semibold">Diskominfo Kota Tomohon</p>
                    <p class="text-xs text-sky-200">Dinas Komunikasi dan Informatika</p>
                </div>
            </div>

            <div class="relative">
                <h1 class="max-w-md text-4xl leading-tight font-bold tracking-tight text-balance xl:text-[42px]">
                    Monitoring dan pemetaan domain OPD dalam satu tempat.
                </h1>
                <p class="mt-4 max-w-md text-base text-sky-100/90">
                    Pantau masa berlaku domain milik perangkat daerah Kota Tomohon dan tangani sebelum layanan publik terganggu.
                </p>

                <ul class="mt-10 space-y-4" role="list">
                    @foreach ([
                    ['shield-check', 'Peringatan dini', 'Domain yang mendekati expired ditandai sesuai tingkat urgensinya.'],
                    ['chart-pie', 'Pemetaan per OPD', 'Lihat sebaran kepemilikan domain di setiap perangkat daerah.'],
                    ['file-text', 'Laporan siap cetak', 'Ekspor data ke PDF dan Excel untuk kebutuhan pelaporan.'],
                    ] as [$ikon, $judul, $teks])
                    <li class="flex gap-4">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15">
                            <x-dynamic-component :component="'lucide-'.$ikon" class="size-5 text-sky-200" aria-hidden="true" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold">{{ $judul }}</p>
                            <p class="mt-0.5 text-sm text-sky-100/80">{{ $teks }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-sky-200/80">&copy; {{ now()->year }} Pemerintah Kota Tomohon</p>
        </aside>

        {{-- Panel form --}}
        <main class="relative flex flex-1 flex-col items-center justify-center px-5 py-12 sm:px-8">
            <button
                type="button"
                x-data
                @click="$store.theme.toggle()"
                class="focus-ring absolute top-5 right-5 inline-flex size-10 items-center justify-center rounded-full text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink"
                aria-label="Ganti tema terang/gelap">
                <x-lucide-moon class="size-5 dark:hidden" aria-hidden="true" />
                <x-lucide-sun class="hidden size-5 dark:block" aria-hidden="true" />
            </button>

            <div class="w-full max-w-sm">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/logo-tomohon-128.png') }}" alt="" class="size-12 shrink-0 object-contain" width="48" height="48">
                    <div class="leading-tight">
                        <p class="text-sm font-bold text-ink">Monitor Domain OPD</p>
                        <p class="text-xs text-ink-muted">Diskominfo Kota Tomohon</p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold tracking-tight text-ink">Masuk ke akun Anda</h2>
                <p class="mt-1.5 text-sm text-ink-muted">Khusus pegawai Diskominfo Kota Tomohon.</p>

                @if ($errors->any())
                <div role="alert" class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                    <x-lucide-triangle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <p>{{ $errors->first() }}</p>
                </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" x-data="{ mengirim: false, lihat: false }" @submit="mengirim = true" class="mt-6 space-y-5" novalidate>
                    @csrf

                    <x-input
                        name="email"
                        label="Email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        inputmode="email"
                        :show-error="false" />

                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-ink">
                            Password <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="lihat ? 'text' : 'password'"
                                type="password"
                                required
                                autocomplete="current-password"
                                @class(['field pr-12', 'field-invalid'=> $errors->has('password')])
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                            >
                            <button
                                type="button"
                                @click="lihat = ! lihat"
                                class="focus-ring absolute top-1/2 right-1.5 inline-flex size-9 -translate-y-1/2 items-center justify-center rounded-lg text-ink-subtle hover:bg-surface-muted hover:text-ink"
                                :aria-label="lihat ? 'Sembunyikan password' : 'Tampilkan password'"
                                :aria-pressed="lihat.toString()">
                                <x-lucide-eye x-show="! lihat" class="size-4" aria-hidden="true" />
                                <x-lucide-eye-off x-show="lihat" x-cloak class="size-4" aria-hidden="true" />
                            </button>
                        </div>
                        @error('password')
                        <p id="password-error" class="text-xs font-medium text-red-700 dark:text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink-muted select-none">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="focus-ring size-4 rounded border-line-strong accent-primary">
                        Ingat saya di perangkat ini
                    </label>

                    <x-button icon="log-in" loading="mengirim" class="w-full">Masuk</x-button>
                </form>

                <p class="mt-8 text-center text-xs text-ink-subtle">
                    Lupa password? Hubungi Super Admin Diskominfo.
                </p>
            </div>
        </main>
    </div>
</x-layouts.guest>
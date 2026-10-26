{{--
    Tumpukan toast global. Otomatis menampilkan session flash `success` dan `error`,
    serta mendengarkan event window `toast` ({ type, message, title }).
--}}

@php
    $initial = collect([
        session('success') ? ['type' => 'success', 'message' => session('success')] : null,
        session('error') ? ['type' => 'error', 'message' => session('error'), 'duration' => 8000] : null,
    ])->filter()->values();
@endphp

<div
    x-data="toastStack(@js($initial))"
    @toast.window="push($event.detail)"
    role="region"
    aria-label="Notifikasi"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[70] flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
>
    {{-- Wilayah live untuk pembaca layar. --}}
    <div class="sr-only" role="status" aria-live="polite" aria-atomic="true">
        <template x-for="toast in items" :key="'sr-' + toast.id">
            <span x-text="toast.message"></span>
        </template>
    </div>

    <template x-for="toast in items" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border border-line bg-surface p-4 shadow-pop"
        >
            <span
                class="mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-full"
                :class="toast.type === 'error'
                    ? 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300'
                    : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'"
            >
                <x-lucide-circle-check x-show="toast.type !== 'error'" class="size-4.5" aria-hidden="true" />
                <x-lucide-triangle-alert x-show="toast.type === 'error'" class="size-4.5" aria-hidden="true" />
            </span>
            <div class="min-w-0 flex-1 pt-0.5">
                <p class="text-sm font-semibold text-ink" x-text="toast.title ?? (toast.type === 'error' ? 'Gagal' : 'Berhasil')"></p>
                <p class="mt-0.5 text-sm text-ink-muted" x-text="toast.message"></p>
            </div>
            <button type="button" @click="dismiss(toast.id)" class="focus-ring -m-1 inline-flex size-8 shrink-0 items-center justify-center rounded-lg text-ink-subtle hover:bg-surface-muted hover:text-ink" aria-label="Tutup notifikasi">
                <x-lucide-x class="size-4" aria-hidden="true" />
            </button>
        </div>
    </template>
</div>

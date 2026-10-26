{{--
    Modal konfirmasi hapus global (satu instance di layout).
    Buka dengan: $dispatch('confirm-delete', { action, title, message })
    atau gunakan komponen <x-delete-button>.
--}}

<div
    x-data="confirmDialog"
    @confirm-delete.window="show($event.detail)"
    @keydown.escape.window="close()"
>
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="judul-konfirmasi"
        aria-describedby="pesan-konfirmasi"
    >
        <div
            x-show="open"
            x-transition.opacity.duration.200ms
            @click="close()"
            class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"
            aria-hidden="true"
        ></div>

        <div
            x-show="open"
            x-trap.noscroll.inert="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-3 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-md rounded-2xl border border-line bg-surface p-6 shadow-pop"
        >
            <div class="flex items-start gap-4">
                <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300">
                    <x-lucide-trash-2 class="size-5" aria-hidden="true" />
                </span>
                <div class="min-w-0 flex-1">
                    <h2 id="judul-konfirmasi" class="text-base font-semibold text-ink" x-text="title"></h2>
                    <p id="pesan-konfirmasi" class="mt-1.5 text-sm text-ink-muted" x-text="message"></p>
                </div>
            </div>

            <form x-ref="form" method="POST" :action="action" @submit.prevent="submit()" class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                @csrf
                @method('DELETE')
                <x-button type="button" variant="secondary" @click="close()" x-bind:disabled="submitting">Batal</x-button>
                <x-button variant="danger" icon="trash-2" loading="submitting">
                    <span x-text="confirmLabel">Hapus</span>
                </x-button>
            </form>
        </div>
    </div>
</div>

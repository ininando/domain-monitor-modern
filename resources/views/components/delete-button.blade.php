@props([
    'action',
    'title' => 'Hapus data?',
    'message' => 'Data yang dihapus tidak dapat dikembalikan.',
    'label' => 'Hapus',
])

{{-- Tombol ikon hapus yang membuka modal konfirmasi global. --}}

<button
    type="button"
    @click="$dispatch('confirm-delete', @js(['action' => $action, 'title' => $title, 'message' => $message]))"
    {{ $attributes->class('focus-ring inline-flex size-9 items-center justify-center rounded-lg pointer-coarse:size-11 text-ink-muted transition-colors duration-150 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-500/10 dark:hover:text-red-300') }}
    aria-label="{{ $label }}"
    title="{{ $label }}"
>
    <x-lucide-trash-2 class="size-4" aria-hidden="true" />
</button>

@props([
    'caption' => null,
    'skeletonRows' => 5,
    'skeletonCols' => 4,
])

{{--
    Tabel dalam kartu. Slot `head` berisi <th>, slot default berisi <tr>.
    Skeleton tampil saat event `memuat` di-dispatch (mis. saat form filter dikirim).
--}}

<div
    x-data="{ memuat: false }"
    @memuat.window="memuat = true"
    @pageshow.window="memuat = false"
    {{ $attributes->class('card relative overflow-hidden') }}
>
    <div class="overflow-x-auto" :aria-busy="memuat.toString()">
        <table class="w-full text-left text-sm">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif
            <thead class="border-b border-line bg-surface-muted/60 text-xs font-semibold tracking-wide text-ink-muted uppercase">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
            <tbody x-show="! memuat" class="divide-y divide-line">
                {{ $slot }}
            </tbody>
            <tbody x-show="memuat" x-cloak aria-hidden="true">
                @for ($i = 0; $i < $skeletonRows; $i++)
                    <tr class="border-b border-line last:border-0">
                        @for ($j = 0; $j < $skeletonCols; $j++)
                            <td class="px-5 py-4">
                                <div class="skeleton h-4 {{ $j === 0 ? 'w-3/4' : 'w-1/2' }}"></div>
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    @isset($footer)
        <div class="border-t border-line px-5 py-3">
            {{ $footer }}
        </div>
    @endisset
</div>

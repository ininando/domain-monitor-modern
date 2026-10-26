@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
    'hideLabel' => false,
])

{{-- options: array [nilai => teks]. Bisa juga isi <option> manual lewat slot. --}}

@php
    $id = $id ?? str_replace(['[', ']', '.'], '-', $name);
    $hasError = $errors->has($name);
    $selected = (string) old($name, $value);
    $describedBy = collect([$hint ? "{$id}-hint" : null, $hasError ? "{$id}-error" : null])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->class('space-y-1.5') }}>
    <label for="{{ $id }}" @class(['block text-sm font-medium text-ink', 'sr-only' => $hideLabel])>
        {{ $label }}
        @if ($required)
            <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span>
        @endif
    </label>

    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @required($required)
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except('class')->class(['field appearance-none pr-10', 'field-invalid' => $hasError]) }}
        >
            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
            {{ $slot }}
        </select>
        <x-lucide-chevron-down class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-subtle" aria-hidden="true" />
    </div>

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-xs text-ink-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-xs font-medium text-red-700 dark:text-red-300">
            <x-lucide-circle-x class="size-3.5 shrink-0" aria-hidden="true" />
            {{ $message }}
        </p>
    @enderror
</div>

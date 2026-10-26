@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
    'required' => false,
    'rows' => 3,
    'id' => null,
])

@php
    $id = $id ?? str_replace(['[', ']', '.'], '-', $name);
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? "{$id}-hint" : null, $hasError ? "{$id}-error" : null])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->class('space-y-1.5') }}>
    <label for="{{ $id }}" class="block text-sm font-medium text-ink">
        {{ $label }}
        @if ($required)
            <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span>
        @endif
    </label>

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @required($required)
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class')->class(['field resize-y', 'field-invalid' => $hasError]) }}
    >{{ old($name, $value) }}</textarea>

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

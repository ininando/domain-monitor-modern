@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
    'showError' => true,
])

@php
    $id = $id ?? str_replace(['[', ']', '.'], '-', $name);
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $hasError = $errors->has($errorKey);
    $describedBy = collect([$hint ? "{$id}-hint" : null, $hasError && $showError ? "{$id}-error" : null])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->class('space-y-1.5') }}>
    <label for="{{ $id }}" class="block text-sm font-medium text-ink">
        {{ $label }}
        @if ($required)
            <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span>
        @endif
    </label>

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($errorKey, $value) }}"
        @required($required)
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class')->class(['field', 'field-invalid' => $hasError]) }}
    >

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-xs text-ink-muted">{{ $hint }}</p>
    @endif

    @if ($showError)
    @error($errorKey)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-xs font-medium text-red-700 dark:text-red-300">
            <x-lucide-circle-x class="size-3.5 shrink-0" aria-hidden="true" />
            {{ $message }}
        </p>
    @enderror
    @endif
</div>

@props([
    'name',
    'value' => null,
    'required' => false,
    'readonly' => false,
    'class' => '',
    'defaultBlank' => false,
    'picker' => false,
])

@php
    use App\Support\ErpDate;

    $resolved = old($name);
    if ($resolved === null) {
        if ($value !== null && $value !== '') {
            $resolved = $picker ? (ErpDate::toStorage($value) ?? '') : ErpDate::display($value);
        } elseif ($defaultBlank) {
            $resolved = '';
        } else {
            $resolved = $picker ? now()->format(ErpDate::STORAGE_FORMAT) : ErpDate::todayDisplay();
        }
    } elseif ($picker && $resolved !== '') {
        $resolved = ErpDate::toStorage($resolved) ?? $resolved;
    }

    $inputClass = 'erp-input ' . ($picker ? 'erp-date-picker ' : 'erp-date-input ') . $class;
@endphp

@if ($picker)
    <input
        {{ $attributes->merge(['class' => $inputClass]) }}
        type="date"
        name="{{ $name }}"
        value="{{ $resolved }}"
        autocomplete="off"
        title="Leave empty to include all dates"
        @if ($required) required @endif
        @if ($readonly) readonly @endif
    >
@else
    <input
        {{ $attributes->merge(['class' => $inputClass]) }}
        type="text"
        name="{{ $name }}"
        value="{{ $resolved }}"
        placeholder="DD-MM-YYYY"
        autocomplete="off"
        inputmode="numeric"
        maxlength="10"
        @if ($required) required @endif
        @if ($readonly) readonly @endif
    >
@endif

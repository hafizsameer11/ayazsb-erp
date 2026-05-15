@props([
    'name',
    'value' => null,
    'required' => false,
    'readonly' => false,
    'class' => '',
])

@php
    use App\Support\ErpDate;

    $display = old($name, $value !== null && $value !== '' ? ErpDate::display($value) : ErpDate::todayDisplay());
@endphp

<input
    {{ $attributes->merge(['class' => 'erp-input erp-date-input ' . $class]) }}
    type="text"
    name="{{ $name }}"
    value="{{ $display }}"
    placeholder="DD-MM-YYYY"
    autocomplete="off"
    inputmode="numeric"
    maxlength="10"
    @if ($required) required @endif
    @if ($readonly) readonly @endif
>

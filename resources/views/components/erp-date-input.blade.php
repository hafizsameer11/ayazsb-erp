@props([
    'name',
    'value' => null,
    'required' => false,
    'readonly' => false,
    'class' => '',
    'defaultBlank' => false,
])

@php
    use App\Support\ErpDate;

    $resolved = old($name);
    if ($resolved === null) {
        if ($value !== null && $value !== '') {
            $resolved = ErpDate::display($value);
        } elseif ($defaultBlank) {
            $resolved = '';
        } else {
            $resolved = ErpDate::todayDisplay();
        }
    }
    $display = $resolved;
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

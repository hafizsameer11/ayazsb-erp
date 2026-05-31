@php
    $selectName = $selectName ?? 'account_id';
    $selectedId = (string) ($selectedId ?? '');
    $required = $required ?? false;
    $targetId = $targetId ?? ('cn-' . md5($selectName));
    $options = $options ?? collect();
    $codeField = $codeField ?? 'code';
    $nameField = $nameField ?? 'name';
    $selectedOption = $options->first(fn ($o) => (string) $o->id === $selectedId);
@endphp
<div class="flex min-w-0 gap-1" data-grey-code-name>
    <select
        class="erp-input w-[7.5rem] shrink-0 font-mono text-[11px]"
        name="{{ $selectName }}"
        data-grey-lookup
        data-grey-lookup-target="#{{ $targetId }}"
        @if($required) required @endif
    >
        <option value=""></option>
        @foreach ($options as $option)
            <option
                value="{{ $option->id }}"
                data-code="{{ $option->{$codeField} }}"
                data-name="{{ $option->{$nameField} }}"
                @selected($selectedId === (string) $option->id)
            >{{ $option->{$codeField} }}</option>
        @endforeach
    </select>
    <input
        class="erp-input min-w-0 flex-1 bg-[#f8f8f8] text-[11px]"
        id="{{ $targetId }}"
        type="text"
        readonly
        tabindex="-1"
        value="{{ $selectedOption?->{$nameField} ?? '' }}"
    >
</div>

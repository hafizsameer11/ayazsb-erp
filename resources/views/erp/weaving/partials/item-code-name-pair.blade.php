@php
    $selectName = $selectName ?? 'lines[0][item_id]';
    $selectedId = (string) ($selectedId ?? '');
    $targetId = $targetId ?? ('weaving-item-' . md5($selectName));
    $options = $storeItems ?? collect();
    $stockMap = $stockMap ?? [];
    $selectedOption = $options->first(fn ($o) => (string) $o->id === $selectedId);
@endphp
<div class="flex min-w-0 gap-1" data-weaving-item-pair>
    <select
        class="erp-input w-[7rem] shrink-0 font-mono text-[11px]"
        name="{{ $selectName }}"
        data-weaving-item-select
        data-weaving-lookup-target="#{{ $targetId }}"
        data-stock-map='@json($stockMap)'
    >
        <option value=""></option>
        @foreach ($options as $option)
            <option
                value="{{ $option->id }}"
                data-code="{{ $option->code }}"
                data-name="{{ $option->name }}"
                data-unit="{{ $option->unit }}"
                @selected($selectedId === (string) $option->id)
            >{{ $option->code }}</option>
        @endforeach
    </select>
    <input
        class="erp-input min-w-0 flex-1 bg-[#f8f8f8] text-[11px]"
        id="{{ $targetId }}"
        type="text"
        readonly
        tabindex="-1"
        value="{{ $selectedOption?->name ?? '' }}"
    >
</div>

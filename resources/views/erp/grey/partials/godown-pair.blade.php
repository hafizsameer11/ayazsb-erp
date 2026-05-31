@php
    $selectName = $selectName ?? 'from_godown_id';
    $selectedId = (string) ($selectedId ?? '');
    $godowns = $godowns ?? collect();
    $targetId = $targetId ?? ('gd-' . md5($selectName));
    $selectedGodown = $godowns->first(fn ($g) => (string) $g->id === $selectedId);
@endphp
<div class="flex min-w-0 gap-1" data-grey-code-name>
    <select
        class="erp-input w-[3.5rem] shrink-0 font-mono text-[11px]"
        name="{{ $selectName }}"
        data-grey-lookup
        data-grey-lookup-target="#{{ $targetId }}"
    >
        <option value=""></option>
        @foreach ($godowns as $godown)
            <option
                value="{{ $godown->id }}"
                data-code="{{ $godown->id }}"
                data-name="{{ $godown->name }}"
                @selected($selectedId === (string) $godown->id)
            >{{ $godown->id }}</option>
        @endforeach
    </select>
    <input
        class="erp-input min-w-0 flex-1 bg-[#f8f8f8] text-[11px]"
        id="{{ $targetId }}"
        type="text"
        readonly
        tabindex="-1"
        value="{{ $selectedGodown?->name ?? '' }}"
    >
</div>

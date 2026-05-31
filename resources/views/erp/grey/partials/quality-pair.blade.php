@php
    $selectName = $selectName ?? 'lines[0][meta][grey_quality_id]';
    $selectedId = (string) ($selectedId ?? '');
    $qualities = $greyQualities ?? collect();
    $targetId = $targetId ?? ('gq-' . md5($selectName));
    $selectedQuality = $qualities->first(fn ($q) => (string) $q->id === $selectedId);
@endphp
<div class="flex min-w-0 gap-1" data-grey-code-name>
    <select
        class="erp-input w-[5.5rem] shrink-0 font-mono text-[11px]"
        name="{{ $selectName }}"
        data-grey-quality-lookup
        data-grey-lookup-target="#{{ $targetId }}"
    >
        <option value=""></option>
        @foreach ($qualities as $quality)
            <option
                value="{{ $quality->id }}"
                data-code="{{ $quality->quality_no }}"
                data-name="{{ $quality->quality_name }}"
                @selected($selectedId === (string) $quality->id)
            >{{ $quality->quality_no }}</option>
        @endforeach
    </select>
    <input
        class="erp-input min-w-0 flex-1 bg-[#f8f8f8] text-[11px]"
        id="{{ $targetId }}"
        type="text"
        readonly
        tabindex="-1"
        value="{{ $selectedQuality?->quality_name ?? '' }}"
    >
</div>

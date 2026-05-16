@php
    $editingLabel = $editingLabel ?? 'record';
    $cancelUrl = $cancelUrl ?? url()->current();
@endphp
<div class="mb-2 flex flex-wrap items-center justify-between gap-2 border border-amber-500 bg-amber-50 px-2 py-1 text-[11px] text-amber-950">
    <span>Editing <strong>{{ $editingLabel }}</strong></span>
    <a
        href="{{ $cancelUrl }}"
        class="rounded border border-slate-500 bg-white px-2 py-0.5 font-semibold hover:bg-slate-50"
    >Cancel</a>
</div>

@php
    $editUrl = $editUrl ?? null;
    $editPermission = $editPermission ?? null;
@endphp
@if ($editUrl && $editPermission && auth()->user()?->hasPermission($editPermission))
    <a
        href="{{ $editUrl }}"
        class="rounded border border-slate-500 bg-white px-2 py-0.5 text-[11px] hover:bg-amber-50"
    >Edit</a>
@endif

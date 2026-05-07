@php
    $actions = $actions ?? ['Voucher print'];
@endphp
<div class="flex flex-wrap gap-2 pt-2">
    @if (auth()->user()?->hasPermission(($permissionPrefix ?? 'accounts.dashboard') . '.create') || auth()->user()?->hasPermission(($permissionPrefix ?? 'accounts.dashboard') . '.edit'))
        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold shadow-sm hover:bg-white">
            Save voucher
        </button>
    @endif
    @foreach ($actions as $action)
        @php
            $actionKey = \Illuminate\Support\Str::of($action)->lower()->replace(' ', '-')->value();
            $actionPermission = str_contains($actionKey, 'print') ? 'print' : 'view';
        @endphp
        @if (auth()->user()?->hasPermission(($permissionPrefix ?? 'accounts.dashboard') . '.' . $actionPermission))
            <button type="button" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold shadow-sm hover:bg-white">
                {{ $action }}
            </button>
        @endif
    @endforeach
</div>


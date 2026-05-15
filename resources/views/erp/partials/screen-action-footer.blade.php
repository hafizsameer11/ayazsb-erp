@php
    $actions = collect($actions ?? ['Print']);
    $secondaryActions = $actions
        ->filter(fn (string $action) => ! in_array(strtolower($action), ['save', 'post voucher', 'post'], true))
        ->values();
    $permissionPrefix = $permissionPrefix ?? null;
    $showSave = $showSave ?? true;
@endphp
<div class="flex flex-wrap justify-between gap-3 border border-slate-300 bg-[#f0f0f0] p-2">
    <div class="flex flex-wrap gap-2">
        @if ($showSave)
            @if ($permissionPrefix === null || auth()->user()?->hasPermission($permissionPrefix . '.create'))
                <button
                    type="submit"
                    name="submit_action"
                    value="post"
                    class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold shadow-sm hover:bg-white"
                >
                    Save
                </button>
            @endif
        @endif
        @foreach ($secondaryActions as $action)
            @php
                $actionKey = \Illuminate\Support\Str::of($action)->lower()->replace(' ', '-')->value();
                $actionPermission = match ($actionKey) {
                    'view-report' => 'view',
                    'export' => 'print',
                    'exit' => 'view',
                    default => str_contains($actionKey, 'print') ? 'print' : 'view',
                };
            @endphp
            @if ($permissionPrefix === null || auth()->user()?->hasPermission($permissionPrefix . '.' . $actionPermission))
                <button type="button" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold shadow-sm hover:bg-white">
                    {{ $action }}
                </button>
            @endif
        @endforeach
    </div>
    <div class="flex items-end gap-2 text-[11px]">
        <label class="erp-field"><span class="erp-label">Total</span><input class="erp-input w-28 text-right font-mono" type="text" value="0.00" readonly></label>
    </div>
</div>

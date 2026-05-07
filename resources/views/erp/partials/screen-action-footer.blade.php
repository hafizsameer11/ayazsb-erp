@php
    $actions = $actions ?? ['Save', 'Print', 'Post voucher'];
    $permissionPrefix = $permissionPrefix ?? null;
@endphp
<div class="flex flex-wrap justify-between gap-3 border border-slate-300 bg-[#f0f0f0] p-2">
    <div class="flex flex-wrap gap-2">
        @foreach ($actions as $action)
            @php
                $actionKey = \Illuminate\Support\Str::of($action)->lower()->replace(' ', '-')->value();
                $actionPermission = match ($actionKey) {
                    'save' => 'edit',
                    'post-voucher' => 'post',
                    'view-report' => 'view',
                    'export' => 'print',
                    'exit' => 'view',
                    default => str_contains($actionKey, 'print') ? 'print' : 'view',
                };
            @endphp
            @if ($permissionPrefix === null || auth()->user()?->hasPermission($permissionPrefix . '.' . $actionPermission))
                <button type="{{ \Illuminate\Support\Str::of($action)->lower()->value() === 'save' ? 'submit' : 'button' }}" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold shadow-sm hover:bg-white">
                    {{ $action }}
                </button>
            @endif
        @endforeach
    </div>
    <div class="flex items-end gap-2 text-[11px]">
        <label class="erp-field"><span class="erp-label">Total</span><input class="erp-input w-28 text-right font-mono" type="text" value="0.00" readonly></label>
    </div>
</div>


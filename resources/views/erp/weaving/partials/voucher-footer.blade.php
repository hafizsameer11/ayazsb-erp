@php
    $meta = $meta ?? [];
    $editingTransaction = $editingTransaction ?? null;
    $voucher = $editingTransaction?->voucher;
@endphp
@include('erp.grey.partials.voucher-strip', ['meta' => array_merge($meta, [
    'voucher_id' => $voucher?->id ?? ($meta['voucher_id'] ?? ''),
    'voucher_num' => $voucher?->voucher_number ?? ($meta['voucher_num'] ?? ''),
    'voucher_date' => optional($voucher?->voucher_date)->format('Y-m-d') ?? ($meta['voucher_date'] ?? ''),
    'voucher_type' => $voucher?->voucher_type ?? ($meta['voucher_type'] ?? data_get(config('weaving_vouchers.screens.' . ($screen['slug'] ?? '')), 'voucher_type', 'JV')),
    'voucher_posted' => ($voucher?->status ?? '') === 'posted',
])])
<div class="grid gap-2 border border-t-0 border-slate-400 bg-[#f0f0f0] p-2 md:grid-cols-4 lg:grid-cols-6">
    <label class="erp-field"><span class="erp-label">GP No</span><input class="erp-input" name="meta[gp_no]" value="{{ $meta['gp_no'] ?? '' }}"></label>
    <label class="erp-field"><span class="erp-label">GP Date</span><input class="erp-input" type="date" name="meta[gp_date]" value="{{ $meta['gp_date'] ?? '' }}"></label>
    @if ($editingTransaction)
        <div class="flex flex-wrap items-end gap-2 md:col-span-2">
            @allowed($permissionPrefix . '.post')
                @if (! $editingTransaction->voucher_id)
                    <form action="{{ route('erp.weaving.screen.voucher', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) }}" method="post" class="inline">
                        @csrf
                        <button type="submit" class="rounded border border-blue-700 bg-blue-100 px-3 py-1 text-[11px] font-semibold hover:bg-blue-50">Generate Voucher</button>
                    </form>
                @else
                    <form action="{{ route('erp.weaving.screen.voucher.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) }}" method="post" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded border border-amber-700 bg-amber-100 px-3 py-1 text-[11px] font-semibold hover:bg-amber-50">Update Voucher</button>
                    </form>
                @endif
            @endallowed
            @allowed($permissionPrefix . '.delete')
                <form action="{{ route('erp.weaving.screen.destroy', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) }}" method="post" class="inline" data-erp-record-delete>
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded border border-red-700 bg-red-100 px-3 py-1 text-[11px] font-semibold hover:bg-red-50">Delete</button>
                </form>
            @endallowed
        </div>
    @endif
</div>

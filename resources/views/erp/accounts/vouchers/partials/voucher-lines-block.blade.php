@php
    $initialRows = $initialRows ?? 3;
@endphp
<div data-erp-detail-lines data-name-prefix="lines" class="space-y-1">
    <div class="overflow-x-auto border border-slate-400">
        <table class="{{ $tableClass ?? 'w-full min-w-[560px] border-collapse text-left text-[12px]' }}">
            <thead>
                @include($theadPartial)
            </thead>
            <tbody data-erp-detail-lines-body>
                @for ($i = 0; $i < $initialRows; $i++)
                    @include($linePartial, ['i' => $i, 'voucherCode' => $voucherCode ?? ''])
                @endfor
            </tbody>
        </table>
    </div>
    <template data-erp-detail-line-template>
        @include($linePartial, ['i' => 0, 'voucherCode' => $voucherCode ?? ''])
    </template>
    @include('erp.partials.erp-add-line-row')
</div>

@php
    $formId = $formId ?? '';
    $voucherTitle = $voucherTitle ?? '';
    $voucherCode = $voucherCode ?? '';
@endphp
<div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
    {{ $formId }} — {{ $voucherTitle }} — {{ $voucherCode }}
</div>
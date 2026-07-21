@include('erp.partials.records-history', [
    'historyType' => 'voucher',
    'historyTitle' => 'Posted vouchers (this screen)',
    'historyScrollMin' => 'min-h-[280px]',
    'historyEmpty' => 'No posted vouchers for this type yet. Use Save above; saved documents will list here grouped by date.',
    'historyFooter' => ($voucherSlug ?? '') === 'jv'
        ? 'Vouchers are posted immediately. Journal voucher requires debit and credit to be equal.'
        : 'Vouchers are posted immediately. Debit and credit do not need to match on this screen.',
    'recordsForDay' => $recordsForDay ?? collect(),
    'historyDate' => $historyDate ?? null,
    'historyNav' => $historyNav ?? [],
    'permissionPrefix' => $permissionPrefix ?? null,
    'voucherSlug' => $voucherSlug ?? 'jv',
])

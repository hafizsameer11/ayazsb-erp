@include('erp.partials.records-history', [
    'historyType' => 'voucher',
    'historyTitle' => 'Posted vouchers (this screen)',
    'historyScrollMin' => 'min-h-[280px]',
    'historyEmpty' => 'No posted vouchers for this type yet. Use Save above; saved documents will list here grouped by date.',
    'historyFooter' => 'Vouchers are posted immediately. Debit and credit must be equal before the system accepts the document.',
    'recordsHistory' => $recordsHistory ?? null,
    'recordsHistoryGrouped' => $recordsHistoryGrouped ?? collect(),
    'permissionPrefix' => $permissionPrefix ?? null,
])

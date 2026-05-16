@include('erp.partials.records-history', [
    'historyType' => 'transaction',
    'historyTitle' => 'Saved transactions',
    'recordsForDay' => $recordsForDay ?? collect(),
    'historyDate' => $historyDate ?? null,
    'historyNav' => $historyNav ?? [],
    'moduleKey' => $moduleKey ?? 'yarn',
    'screen' => $screen ?? ['slug' => ''],
    'permissionPrefix' => $permissionPrefix ?? null,
])

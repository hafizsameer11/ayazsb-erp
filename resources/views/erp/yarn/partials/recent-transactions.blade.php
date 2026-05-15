@include('erp.partials.records-history', [
    'historyType' => 'transaction',
    'historyTitle' => 'Saved transactions',
    'recordsHistory' => $recordsHistory ?? null,
    'recordsHistoryGrouped' => $recordsHistoryGrouped ?? collect(),
    'moduleKey' => $moduleKey ?? 'yarn',
    'screen' => $screen ?? ['slug' => ''],
    'permissionPrefix' => $permissionPrefix ?? null,
])

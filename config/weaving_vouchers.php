<?php

return [
    /*
    | Screen voucher rules — party / CC / stock accounts come from the form and
    | Weaving Master Data → Account Mapping (sub-ledgers), not from .env.
    */
    'screens' => [
        'store-issue' => [
            'voucher_type' => 'JV',
            'requires_party' => false,
            'entries' => [
                ['side' => 'debit', 'source' => 'cc', 'per_line' => true],
                ['side' => 'credit', 'source' => 'store_stock', 'per_line' => false],
            ],
        ],
        'purchase-order' => [
            'voucher_type' => 'BPV',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'store_stock', 'per_line' => false],
                ['side' => 'credit', 'source' => 'party', 'per_line' => false],
            ],
        ],
        'purchase-return' => [
            'voucher_type' => 'BRV',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'party', 'per_line' => false],
                ['side' => 'credit', 'source' => 'store_stock', 'per_line' => false],
            ],
        ],
        'yarn-receipt' => [
            'voucher_type' => 'BPV',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'yarn_stock', 'per_line' => false],
                ['side' => 'credit', 'source' => 'party', 'per_line' => false],
            ],
        ],
        'yarn-return-stock-to-party' => [
            'voucher_type' => 'BRV',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'party', 'per_line' => false],
                ['side' => 'credit', 'source' => 'yarn_stock', 'per_line' => false],
            ],
        ],
        'yarn-issuance-to-sizing' => [
            'voucher_type' => 'JV',
            'requires_party' => false,
            'requires_sizing_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'sizing_expense', 'per_line' => false],
                ['side' => 'credit', 'source' => 'yarn_stock', 'per_line' => false],
            ],
        ],
        'set-receipt-details' => [
            'voucher_type' => 'BPV',
            'requires_party' => false,
            'requires_sizing_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'sizing_expense', 'per_line' => false],
                ['side' => 'credit', 'source' => 'sizing_party', 'per_line' => false],
            ],
        ],
        'fabric-issue-sale-kachi' => [
            'voucher_type' => 'CR',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'party', 'per_line' => false],
                ['side' => 'credit', 'source' => 'fabric_sales', 'per_line' => false],
            ],
        ],
        'fabric-issue-conversion-kachi' => [
            'voucher_type' => 'JV',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'fabric_cogs', 'per_line' => false],
                ['side' => 'credit', 'source' => 'grey_stock', 'per_line' => false],
            ],
        ],
        'fabric-issue-conversion-pachi' => [
            'voucher_type' => 'JV',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'party', 'per_line' => false],
                ['side' => 'credit', 'source' => 'grey_stock', 'per_line' => false],
            ],
        ],
        'rejection-sale' => [
            'voucher_type' => 'CR',
            'requires_party' => true,
            'entries' => [
                ['side' => 'debit', 'source' => 'party', 'per_line' => false],
                ['side' => 'credit', 'source' => 'fabric_sales', 'per_line' => false],
            ],
        ],
    ],
];

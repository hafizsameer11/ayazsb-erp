<?php

return [
    /*
    | Yarn purchase/sale screens that auto-post accounting vouchers on save/post.
    | Party account comes from the transaction; stock/sales accounts from Yarn Master Data → Account Mapping.
    */
    'screens' => [
        'purchase-contract-wise' => [
            'voucher_type' => 'YPV',
            'direction' => 'purchase',
            'entries' => [
                ['side' => 'debit', 'source' => 'yarn_stock'],
                ['side' => 'credit', 'source' => 'party'],
            ],
        ],
        'purchase-without-contract' => [
            'voucher_type' => 'YPV',
            'direction' => 'purchase',
            'entries' => [
                ['side' => 'debit', 'source' => 'yarn_stock'],
                ['side' => 'credit', 'source' => 'party'],
            ],
        ],
        'sale-contract-wise' => [
            'voucher_type' => 'YSV',
            'direction' => 'sale',
            'entries' => [
                ['side' => 'debit', 'source' => 'party'],
                ['side' => 'credit', 'source' => 'yarn_sales'],
            ],
        ],
        'sale-without-contract' => [
            'voucher_type' => 'YSV',
            'direction' => 'sale',
            'entries' => [
                ['side' => 'debit', 'source' => 'party'],
                ['side' => 'credit', 'source' => 'yarn_sales'],
            ],
        ],
    ],
];

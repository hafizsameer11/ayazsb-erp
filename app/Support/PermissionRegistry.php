<?php

namespace App\Support;

class PermissionRegistry
{
    /**
     * @var list<string>
     */
    public const ACTIONS = ['view', 'create', 'edit', 'delete', 'post', 'print'];

    /**
     * @return array<string, string>
     */
    public static function routePermissionMap(): array
    {
        return [
            'erp.accounts.dashboard' => 'accounts.dashboard.view',
            'erp.accounts.coa' => 'accounts.coa.view',
            'erp.accounts.coa.store' => 'accounts.coa.create',
            'erp.accounts.coa.update' => 'accounts.coa.edit',
            'erp.profile' => 'accounts.dashboard.view',
            'erp.accounts.opening' => 'accounts.opening.view',
            'erp.accounts.opening.store' => 'accounts.opening.create',
            'erp.accounts.financial-year' => 'accounts.financial-year.view',
            'erp.accounts.financial-year.store' => 'accounts.financial-year.create',
            'erp.accounts.vouchers.jv' => 'accounts.vouchers.jv.view',
            'erp.accounts.vouchers.cp' => 'accounts.vouchers.cp.view',
            'erp.accounts.vouchers.cr' => 'accounts.vouchers.cr.view',
            'erp.accounts.vouchers.bpv' => 'accounts.vouchers.bpv.view',
            'erp.accounts.vouchers.brv' => 'accounts.vouchers.brv.view',
            'erp.accounts.vouchers.cv' => 'accounts.vouchers.cv.view',
            'erp.yarn.dashboard' => 'yarn.dashboard.view',
            'erp.yarn.screen' => 'yarn.screen.view',
            'erp.grey.dashboard' => 'grey.dashboard.view',
            'erp.grey.screen' => 'grey.screen.view',
            'erp.reports.dashboard' => 'reports.dashboard.view',
            'erp.reports.screen' => 'reports.screen.view',
            'erp.reports.view' => 'reports.dashboard.view',
            'erp.reports.export' => 'reports.dashboard.print',
            'erp.reports.print' => 'reports.dashboard.print',
            'erp.docs' => 'accounts.dashboard.view',
            'erp.admin.dashboard' => 'admin.dashboard.view',
            'erp.admin.users' => 'admin.users.view',
            'erp.admin.users.update-roles' => 'admin.users.edit',
            'erp.admin.roles' => 'admin.roles.view',
            'erp.admin.roles.store' => 'admin.roles.create',
            'erp.admin.roles.update' => 'admin.roles.edit',
            'erp.admin.roles.delete' => 'admin.roles.delete',
            'erp.admin.roles.permissions' => 'admin.roles.edit',
        ];
    }

    /**
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        $prefixes = [
            'accounts.dashboard',
            'accounts.coa',
            'accounts.opening',
            'accounts.financial-year',
            'accounts.vouchers.jv',
            'accounts.vouchers.cp',
            'accounts.vouchers.cr',
            'accounts.vouchers.bpv',
            'accounts.vouchers.brv',
            'accounts.vouchers.cv',
            'yarn.dashboard',
            'grey.dashboard',
            'reports.dashboard',
            'admin.dashboard',
            'admin.users',
            'admin.roles',
        ];

        foreach (self::moduleScreens() as $module => $screens) {
            foreach ($screens as $screen) {
                $prefixes[] = $module . '.' . $screen;
            }
        }

        $permissions = [];

        foreach (array_unique($prefixes) as $prefix) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = "{$prefix}.{$action}";
            }
        }

        sort($permissions);

        return $permissions;
    }

    /**
     * @return array<string, list<string>>
     */
    private static function moduleScreens(): array
    {
        return [
            'yarn' => [
                'purchase-contract',
                'purchase-contract-wise',
                'purchase-without-contract',
                'sale-contract',
                'sale-contract-wise',
                'sale-without-contract',
                'issuance',
                'receipt-processed',
                'receipt-processed-auto',
                'issuance-return',
                'issuance-transfer',
                'godown-transfer',
                'loom-transfer',
                'gain-shortage',
                'master-yarn',
                'master-items',
                'master-godowns',
                'opening',
            ],
            'grey' => [
                'purchase',
                'sale',
                'conversion-contract',
                'conversion-inward',
                'master-grey',
                'master-godowns',
                'opening',
            ],
            'reports' => [
                'accounts',
                'yarn',
                'grey',
            ],
        ];
    }
}


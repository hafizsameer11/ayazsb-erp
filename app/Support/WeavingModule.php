<?php

namespace App\Support;

class WeavingModule
{
    /**
     * @return array{label: string, groups: array<string, list<array{slug: string, label: string, code: string}>>}
     */
    public static function definition(): array
    {
        return [
            'label' => 'Weaving',
            'groups' => [
                'Store' => [
                    ['slug' => 'store-issue', 'label' => 'Store Issue', 'code' => 'WEAVSP_0001'],
                    ['slug' => 'purchase-order', 'label' => 'Purchase Order', 'code' => 'WEAVSP_0002'],
                    ['slug' => 'purchase-return', 'label' => 'Purchase Return', 'code' => 'WEAVSP_0003'],
                ],
                'Beams' => [
                    ['slug' => 'set-receipt-details', 'label' => 'Set Receipt Details', 'code' => 'WEAVSP_0010'],
                    ['slug' => 'sized-beams-issuance', 'label' => 'Sized Beams Issuance', 'code' => 'WEAVSP_0011'],
                ],
                'Weaving Yarn' => [
                    ['slug' => 'yarn-receipt', 'label' => 'Yarn Receipt', 'code' => 'WEAVSP_0020'],
                    ['slug' => 'yarn-stock-adjustment', 'label' => 'Yarn Stock Adjustment', 'code' => 'WEAVSP_0021'],
                    ['slug' => 'yarn-issuance-to-sizing', 'label' => 'Yarn Issuance to Sizing', 'code' => 'WEAVSP_0022'],
                    ['slug' => 'yarn-return-sizing-to-stock', 'label' => 'Yarn Return Sizing to Stock', 'code' => 'WEAVSP_0023'],
                    ['slug' => 'yarn-issuance-stock-to-production', 'label' => 'Yarn Issuance (Stock to Production)', 'code' => 'WEAVSP_0024'],
                    ['slug' => 'yarn-return-production-to-stock', 'label' => 'Yarn Return Production to Stock', 'code' => 'WEAVSP_0025'],
                    ['slug' => 'yarn-return-stock-to-party', 'label' => 'Yarn Return Stock to Party', 'code' => 'WEAVSP_0026'],
                ],
                'Weaving Grey' => [
                    ['slug' => 'production-data-entry', 'label' => 'Production Data Entry', 'code' => 'WEAVSP_0030'],
                    ['slug' => 'grey-stock-adjustment', 'label' => 'Grey Stock Adjustment', 'code' => 'WEAVSP_0031'],
                    ['slug' => 'rejection-receipt-packi-parchi', 'label' => 'Rejection Receipt Packi Parchi', 'code' => 'WEAVSP_0032'],
                    ['slug' => 'mending-form', 'label' => 'Mending Form', 'code' => 'WEAVSP_0033'],
                    ['slug' => 'fabric-issue-conversion-kachi', 'label' => 'Fabric Issue (Conversion Kachi Parchi)', 'code' => 'WEAVSP_0034'],
                    ['slug' => 'fabric-issue-conversion-pachi', 'label' => 'Fabric Issue (Conversion Pachi Parchi)', 'code' => 'WEAVSP_0035'],
                    ['slug' => 'fabric-issue-sale-kachi', 'label' => 'Fabric Issue (Sale Kachi Parchi)', 'code' => 'WEAVSP_0036'],
                    ['slug' => 'fabric-return', 'label' => 'Fabric Return', 'code' => 'WEAVSP_0037'],
                    ['slug' => 'rejection-sale', 'label' => 'Rejection Sale', 'code' => 'WEAVSP_0038'],
                    ['slug' => 'rejection-stock-quality-transfer', 'label' => 'Rejection Stock Quality Transfer Adjustment', 'code' => 'WEAVSP_0039'],
                ],
                'Setup' => [
                    ['slug' => 'master-data', 'label' => 'Weaving Master Data', 'code' => 'WEAVSP_0004'],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function screenSlugs(): array
    {
        $slugs = [];
        foreach (self::definition()['groups'] as $items) {
            foreach ($items as $item) {
                $slugs[] = $item['slug'];
            }
        }

        return $slugs;
    }

    /**
     * @return array{slug: string, label: string, code: string}|null
     */
    public static function findScreen(string $slug): ?array
    {
        foreach (self::definition()['groups'] as $items) {
            foreach ($items as $item) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Stock pool for yarn screens.
     */
    public static function yarnStockPoolForScreen(string $slug): ?string
    {
        return match ($slug) {
            'yarn-receipt', 'yarn-stock-adjustment', 'yarn-issuance-to-sizing', 'yarn-return-stock-to-party' => 'stock',
            'yarn-return-sizing-to-stock' => 'sizing',
            'yarn-issuance-stock-to-production', 'yarn-return-production-to-stock' => 'production',
            default => null,
        };
    }

    /**
     * @return array{in: string|null, out: string|null}
     */
    public static function yarnMovementForScreen(string $slug): array
    {
        return match ($slug) {
            'yarn-receipt' => ['in' => 'stock', 'out' => null],
            'yarn-issuance-to-sizing' => ['in' => 'sizing', 'out' => 'stock'],
            'yarn-return-sizing-to-stock' => ['in' => 'stock', 'out' => 'sizing'],
            'yarn-issuance-stock-to-production' => ['in' => 'production', 'out' => 'stock'],
            'yarn-return-production-to-stock' => ['in' => 'stock', 'out' => 'production'],
            'yarn-return-stock-to-party' => ['in' => null, 'out' => 'stock'],
            default => ['in' => null, 'out' => null],
        };
    }
}

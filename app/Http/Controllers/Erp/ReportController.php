<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const ALLOWED_SCREENS = ['accounts', 'yarn', 'grey'];

    public function view(Request $request, string $screen): View
    {
        $this->ensurePermission($screen, 'view');

        [$title, $rows, $totals] = $this->buildDataset($request, $screen);

        return view('erp.reports.result', [
            'activeModule' => 'reports',
            'pageTitle' => $title,
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Reports', 'route' => 'erp.reports.dashboard'],
                ['label' => $title],
            ],
            'title' => $title,
            'rows' => $rows,
            'totals' => $totals,
            'screen' => $screen,
            'filters' => $request->all(),
        ]);
    }

    public function export(Request $request, string $screen)
    {
        $this->ensurePermission($screen, 'print');
        [$title, $rows] = $this->buildDataset($request, $screen);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . str_replace(' ', '_', strtolower($title)) . '.csv"',
        ];

        $callback = static function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Date', 'Reference', 'Party', 'Status', 'Qty', 'Amount']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['reference'],
                    $row['party'],
                    $row['status'],
                    $row['qty'],
                    $row['amount'],
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request, string $screen): View
    {
        $this->ensurePermission($screen, 'print');
        [$title, $rows, $totals] = $this->buildDataset($request, $screen);

        return view('erp.reports.print', [
            'title' => $title,
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }

    /**
     * @return array{0:string,1:array<int,array<string,mixed>>,2:array<string,float>}
     */
    private function buildDataset(Request $request, string $screen): array
    {
        if ($screen === 'accounts') {
            $query = Voucher::query()->with('party');
            if ($request->filled('from_date')) {
                $query->whereDate('voucher_date', '>=', $request->string('from_date')->toString());
            }
            if ($request->filled('to_date')) {
                $query->whereDate('voucher_date', '<=', $request->string('to_date')->toString());
            }

            $rows = $query->latest()->get()->map(static fn (Voucher $voucher) => [
                'date' => $voucher->voucher_date,
                'reference' => $voucher->voucher_type . '-' . $voucher->voucher_number,
                'party' => $voucher->party?->name ?? '-',
                'status' => $voucher->status,
                'qty' => 0,
                'amount' => (float) $voucher->total_amount,
            ])->all();

            return [
                'Accounts Report',
                $rows,
                [
                    'qty' => 0.0,
                    'amount' => array_sum(array_column($rows, 'amount')),
                ],
            ];
        }

        $module = $screen === 'grey' ? 'grey' : 'yarn';
        $query = InventoryTransaction::query()->where('module', $module)->with('party');
        if ($request->filled('from_date')) {
            $query->whereDate('trans_date', '>=', $request->string('from_date')->toString());
        }
        if ($request->filled('to_date')) {
            $query->whereDate('trans_date', '<=', $request->string('to_date')->toString());
        }

        $rows = $query->latest()->get()->map(static fn (InventoryTransaction $transaction) => [
            'date' => $transaction->trans_date,
            'reference' => $transaction->screen_slug . '-' . $transaction->trans_no,
            'party' => $transaction->party?->name ?? '-',
            'status' => $transaction->status,
            'qty' => (float) $transaction->total_qty,
            'amount' => (float) $transaction->total_amount,
        ])->all();

        return [
            ucfirst($module) . ' Report',
            $rows,
            [
                'qty' => array_sum(array_column($rows, 'qty')),
                'amount' => array_sum(array_column($rows, 'amount')),
            ],
        ];
    }

    private function ensurePermission(string $screen, string $action): void
    {
        abort_unless(in_array($screen, self::ALLOWED_SCREENS, true), 404);
        $user = Auth::user();
        abort_unless($user instanceof \App\Models\User && $user->hasPermission("reports.{$screen}.{$action}"), 403);
    }
}

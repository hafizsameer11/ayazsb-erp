<?php

namespace App\Http\Controllers\Erp;

use App\Http\Concerns\AuthorizesWeaving;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\WeavingItemNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeavingItemsController extends Controller
{
    use AuthorizesWeaving;

    /** @var list<string> */
    public const MEASURE_UNITS = ['PCS', 'BOX', 'KG', 'LBS', 'MTR', 'LTR', 'SET', 'ROLL', 'BAG', 'CARTON'];

    public function show(): View
    {
        abort_unless($this->weavingAllowed('items', 'view'), 403);

        return view('erp.weaving.items', [
            'activeModule' => 'weaving',
            'moduleKey' => 'weaving',
            'moduleLabel' => 'Weaving',
            'permissionPrefix' => 'weaving.items',
            'pageTitle' => 'Weaving Items',
            'screen' => ['slug' => 'items', 'label' => 'Weaving Items', 'code' => 'WEAVSP_0005'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Weaving', 'route' => 'erp.weaving.dashboard'],
                ['label' => 'Weaving Items'],
            ],
            'measureUnits' => self::MEASURE_UNITS,
            'weavingItems' => Item::query()
                ->where('module', 'store')
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function store(Request $request, WeavingItemNumberService $numberService): RedirectResponse
    {
        abort_unless($this->weavingAllowed('items', 'create'), 403);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:items,id'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ]);

        foreach ($data['items'] as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '' && empty($row['id'])) {
                continue;
            }

            if (! empty($row['id'])) {
                Item::query()->whereKey($row['id'])->where('module', 'store')->update([
                    'name' => $name ?: Item::query()->find($row['id'])?->name,
                    'unit' => $row['unit'] ?? 'PCS',
                    'is_active' => ! empty($row['is_active']),
                ]);
                continue;
            }

            if ($name === '') {
                continue;
            }

            Item::query()->create([
                'code' => $numberService->nextCode(),
                'name' => $name,
                'module' => 'store',
                'unit' => $row['unit'] ?? 'PCS',
                'is_active' => ! empty($row['is_active']),
            ]);
        }

        return redirect()->route('erp.weaving.items')->with('status', 'Weaving items saved.');
    }
}

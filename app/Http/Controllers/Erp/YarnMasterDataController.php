<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use App\Models\Item;
use App\Models\YarnBlend;
use App\Models\YarnBrand;
use App\Models\YarnCount;
use App\Models\YarnRatio;
use App\Models\YarnThread;
use App\Services\YarnItemNameBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class YarnMasterDataController extends Controller
{
  private const ITEM_TYPES = ['D/P', 'FRESH'];

  private const WEIGHT_UNITS = ['LBS', 'KG'];

  public function show(Request $request): View|RedirectResponse
  {
    abort_unless($this->allowed('view'), 403);

    $tab = $request->query('tab', 'master');
    if (! in_array($tab, ['master', 'items', 'godowns'], true)) {
      $tab = 'master';
    }

    return view('erp.yarn.master-data', [
      'activeModule' => 'yarn',
      'moduleKey' => 'yarn',
      'moduleLabel' => 'Yarn Management',
      'activeTab' => $tab,
      'permissionPrefix' => 'yarn.master-data',
      'pageTitle' => 'Yarn Master Data',
      'screen' => ['slug' => 'master-data', 'label' => 'Yarn Master Data', 'code' => 'YARNSP_0003'],
      'breadcrumbs' => [
        ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
        ['label' => 'Yarn Management', 'route' => 'erp.yarn.dashboard'],
        ['label' => 'Yarn Master Data'],
      ],
      'yarnCounts' => YarnCount::query()->orderBy('id')->get(),
      'yarnThreads' => YarnThread::query()->orderBy('id')->get(),
      'yarnBlends' => YarnBlend::query()->orderBy('id')->get(),
      'yarnBrands' => YarnBrand::query()->orderBy('id')->get(),
      'yarnRatios' => YarnRatio::query()->orderBy('id')->get(),
      'yarnItems' => Item::query()
        ->where('module', 'yarn')
        ->with(['yarnCount', 'yarnThread', 'yarnBlend', 'yarnBrand', 'yarnRatio'])
        ->orderBy('code')
        ->get(),
      'godowns' => Godown::query()
        ->whereIn('module', ['yarn', 'shared'])
        ->orderBy('id')
        ->get(),
      'itemTypes' => self::ITEM_TYPES,
      'weightUnits' => self::WEIGHT_UNITS,
    ]);
  }

  public function store(Request $request, YarnItemNameBuilder $nameBuilder): RedirectResponse
  {
    abort_unless($this->allowed('create'), 403);

    $data = $request->validate([
      'counts' => ['nullable', 'array'],
      'counts.*.id' => ['nullable', 'integer'],
      'counts.*.count' => ['nullable', 'string', 'max:40'],
      'counts.*.is_active' => ['nullable', 'boolean'],
      'threads' => ['nullable', 'array'],
      'threads.*.id' => ['nullable', 'integer'],
      'threads.*.thread' => ['nullable', 'string', 'max:40'],
      'threads.*.is_active' => ['nullable', 'boolean'],
      'blends' => ['nullable', 'array'],
      'blends.*.id' => ['nullable', 'integer'],
      'blends.*.blend' => ['nullable', 'string', 'max:80'],
      'blends.*.is_active' => ['nullable', 'boolean'],
      'brands' => ['nullable', 'array'],
      'brands.*.id' => ['nullable', 'integer'],
      'brands.*.brand' => ['nullable', 'string', 'max:80'],
      'brands.*.is_active' => ['nullable', 'boolean'],
      'ratios' => ['nullable', 'array'],
      'ratios.*.id' => ['nullable', 'integer'],
      'ratios.*.ratio' => ['nullable', 'string', 'max:40'],
      'ratios.*.is_active' => ['nullable', 'boolean'],
      'items' => ['nullable', 'array'],
      'items.*.id' => ['nullable', 'integer'],
      'items.*.code' => ['nullable', 'string', 'max:40'],
      'items.*.yarn_count_id' => ['nullable', 'integer', 'exists:yarn_counts,id'],
      'items.*.yarn_thread_id' => ['nullable', 'integer', 'exists:yarn_threads,id'],
      'items.*.yarn_blend_id' => ['nullable', 'integer', 'exists:yarn_blends,id'],
      'items.*.yarn_brand_id' => ['nullable', 'integer', 'exists:yarn_brands,id'],
      'items.*.yarn_ratio_id' => ['nullable', 'integer', 'exists:yarn_ratios,id'],
      'items.*.item_type' => ['nullable', 'string', Rule::in(self::ITEM_TYPES)],
      'items.*.pack_size_cones' => ['nullable', 'integer', 'min:0'],
      'items.*.packing_weight' => ['nullable', 'numeric', 'min:0'],
      'items.*.unit' => ['nullable', 'string', Rule::in(self::WEIGHT_UNITS)],
      'items.*.name' => ['nullable', 'string', 'max:255'],
      'items.*.yarn_code' => ['nullable', 'string', 'max:80'],
      'items.*.is_active' => ['nullable', 'boolean'],
      'godowns' => ['nullable', 'array'],
      'godowns.*.id' => ['nullable', 'integer'],
      'godowns.*.name' => ['nullable', 'string', 'max:120'],
      'godowns.*.is_active' => ['nullable', 'boolean'],
      'tab' => ['nullable', 'string', Rule::in(['master', 'items', 'godowns'])],
    ]);

    DB::transaction(function () use ($data, $nameBuilder): void {
      $this->syncLookupRows(YarnCount::class, 'count', $data['counts'] ?? []);
      $this->syncLookupRows(YarnThread::class, 'thread', $data['threads'] ?? []);
      $this->syncLookupRows(YarnBlend::class, 'blend', $data['blends'] ?? []);
      $this->syncLookupRows(YarnBrand::class, 'brand', $data['brands'] ?? []);
      $this->syncLookupRows(YarnRatio::class, 'ratio', $data['ratios'] ?? []);
      $this->syncYarnItems($data['items'] ?? [], $nameBuilder);
      $this->syncGodowns($data['godowns'] ?? []);
    });

    $tab = $data['tab'] ?? 'master';

    return redirect()
      ->route('erp.yarn.master-data', ['tab' => $tab])
      ->with('status', 'Yarn master data saved.');
  }

  /**
   * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
   * @param array<int, array<string, mixed>> $rows
   */
  private function syncLookupRows(string $modelClass, string $valueColumn, array $rows): void
  {
    $keptIds = [];

    foreach ($rows as $row) {
      $value = trim((string) ($row[$valueColumn] ?? ''));
      if ($value === '') {
        continue;
      }

      $attributes = [
        $valueColumn => $value,
        'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
      ];

      if (! empty($row['id'])) {
        $record = $modelClass::query()->find($row['id']);
        if ($record) {
          $record->update($attributes);
          $keptIds[] = $record->id;

          continue;
        }
      }

      $record = $modelClass::query()->create($attributes);
      $keptIds[] = $record->id;
    }

    if ($keptIds !== []) {
      $modelClass::query()->whereNotIn('id', $keptIds)->update(['is_active' => false]);
    }
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   */
  private function syncYarnItems(array $rows, YarnItemNameBuilder $nameBuilder): void
  {
    $keptIds = [];

    foreach ($rows as $row) {
      $code = trim((string) ($row['code'] ?? ''));
      if ($code === '' && empty($row['yarn_blend_id'])) {
        continue;
      }

      $unit = $row['unit'] ?? 'LBS';
      $name = trim((string) ($row['name'] ?? ''));
      if ($name === '') {
        $name = $nameBuilder->build(
          isset($row['yarn_count_id']) ? (int) $row['yarn_count_id'] : null,
          isset($row['yarn_thread_id']) ? (int) $row['yarn_thread_id'] : null,
          isset($row['yarn_blend_id']) ? (int) $row['yarn_blend_id'] : null,
          isset($row['yarn_brand_id']) ? (int) $row['yarn_brand_id'] : null,
          isset($row['yarn_ratio_id']) ? (int) $row['yarn_ratio_id'] : null,
          $row['item_type'] ?? null,
          isset($row['pack_size_cones']) ? (int) $row['pack_size_cones'] : null,
          isset($row['packing_weight']) ? (float) $row['packing_weight'] : null,
          $unit,
        );
      }

      $attributes = [
        'code' => $code !== '' ? $code : null,
        'name' => $name,
        'module' => 'yarn',
        'unit' => $unit,
        'yarn_count_id' => $row['yarn_count_id'] ?? null,
        'yarn_thread_id' => $row['yarn_thread_id'] ?? null,
        'yarn_blend_id' => $row['yarn_blend_id'] ?? null,
        'yarn_brand_id' => $row['yarn_brand_id'] ?? null,
        'yarn_ratio_id' => $row['yarn_ratio_id'] ?? null,
        'item_type' => $row['item_type'] ?? null,
        'pack_size_cones' => $row['pack_size_cones'] ?? null,
        'packing_weight' => $row['packing_weight'] ?? null,
        'yarn_code' => $row['yarn_code'] ?? null,
        'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
      ];

      if (! empty($row['id'])) {
        $item = Item::query()->where('module', 'yarn')->find($row['id']);
        if ($item) {
          if ($attributes['code'] === null) {
            $attributes['code'] = $item->code;
          }
          $item->update($attributes);
          $keptIds[] = $item->id;

          continue;
        }
      }

      if ($attributes['code'] === null) {
        $attributes['code'] = (string) $this->nextYarnItemCode();
      }

      $item = Item::query()->create($attributes);
      $keptIds[] = $item->id;
    }

    if ($keptIds !== []) {
      Item::query()
        ->where('module', 'yarn')
        ->whereNotIn('id', $keptIds)
        ->update(['is_active' => false]);
    }
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   */
  private function syncGodowns(array $rows): void
  {
    $keptIds = [];

    foreach ($rows as $row) {
      $name = trim((string) ($row['name'] ?? ''));
      if ($name === '') {
        continue;
      }

      $attributes = [
        'name' => $name,
        'module' => 'yarn',
        'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
      ];

      if (! empty($row['id'])) {
        $godown = Godown::query()->find($row['id']);
        if ($godown) {
          $godown->update($attributes);
          $keptIds[] = $godown->id;

          continue;
        }
      }

      $godown = Godown::query()->create([
        ...$attributes,
        'code' => 'GD-' . uniqid(),
      ]);
      $godown->update(['code' => (string) $godown->id]);
      $keptIds[] = $godown->id;
    }

    if ($keptIds !== []) {
      Godown::query()
        ->where('module', 'yarn')
        ->whereNotIn('id', $keptIds)
        ->update(['is_active' => false]);
    }
  }

  private function nextYarnItemCode(): int
  {
    $maxNumeric = Item::query()
      ->where('module', 'yarn')
      ->pluck('code')
      ->map(static fn ($code) => is_numeric($code) ? (int) $code : 0)
      ->max();

    return max(1000, (int) $maxNumeric) + 1;
  }

  private function allowed(string $action): bool
  {
    $user = Auth::user();
    if (! $user instanceof \App\Models\User) {
      return false;
    }

    $permissions = [
      "yarn.master-data.{$action}",
      "yarn.master-yarn.{$action}",
      "yarn.master-items.{$action}",
      "yarn.master-godowns.{$action}",
    ];

    foreach ($permissions as $permission) {
      if ($user->hasPermission($permission)) {
        return true;
      }
    }

    return false;
  }
}

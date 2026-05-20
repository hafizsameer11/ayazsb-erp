<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use App\Models\GreyQuality;
use App\Models\GreyQualityDetail;
use App\Models\YarnBlend;
use App\Models\YarnCount;
use App\Models\YarnThread;
use App\Services\GreyQualityNameBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GreyMasterDataController extends Controller
{
    public function show(Request $request, GreyQualityNameBuilder $nameBuilder): View|RedirectResponse
    {
        abort_unless($this->allowed('view'), 403);

        $tab = $request->query('tab', 'master');
        if (! in_array($tab, ['master', 'godowns'], true)) {
            $tab = 'master';
        }

        $editingQuality = null;
        if ($tab === 'master' && $request->filled('edit')) {
            $editingQuality = GreyQuality::query()
                ->with('details.yarnCount', 'details.yarnThread', 'details.yarnBlend')
                ->find($request->integer('edit'));
        }

        return view('erp.grey.master-data', [
            'activeModule' => 'grey',
            'moduleKey' => 'grey',
            'moduleLabel' => 'Grey Management',
            'activeTab' => $tab,
            'permissionPrefix' => 'grey.master-data',
            'pageTitle' => 'Grey Master Data',
            'screen' => ['slug' => 'master-data', 'label' => 'Grey Master Data', 'code' => 'GREYSP_0003'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Grey Management', 'route' => 'erp.grey.dashboard'],
                ['label' => 'Grey Master Data'],
            ],
            'qualities' => GreyQuality::query()->orderBy('quality_no')->get(),
            'editingQuality' => $editingQuality,
            'yarnCounts' => YarnCount::query()->where('is_active', true)->orderBy('id')->get(),
            'yarnThreads' => YarnThread::query()->where('is_active', true)->orderBy('id')->get(),
            'yarnBlends' => YarnBlend::query()->where('is_active', true)->orderBy('id')->get(),
            'godowns' => Godown::query()->whereIn('module', ['grey', 'shared'])->orderBy('id')->get(),
            'tags' => ['CONVERSION', 'PURCHASE', 'SALE', 'GENERAL'],
            'seasons' => ['SUMMER', 'WINTER', 'ALL'],
            'natures' => ['WARP', 'WEFT'],
        ]);
    }

    public function store(Request $request, GreyQualityNameBuilder $nameBuilder): RedirectResponse
    {
        abort_unless($this->allowed('create'), 403);

        $tab = $request->input('tab', 'master');
        if ($tab === 'godowns') {
            return $this->storeGodowns($request);
        }

        return $this->storeQuality($request, $nameBuilder);
    }

    public function destroyQuality(Request $request, GreyQuality $quality): RedirectResponse
    {
        abort_unless($this->allowed('delete'), 403);
        $quality->delete();

        return redirect()
            ->route('erp.grey.master-data', ['tab' => 'master'])
            ->with('status', "Quality {$quality->quality_no} deleted.");
    }

    private function storeQuality(Request $request, GreyQualityNameBuilder $nameBuilder): RedirectResponse
    {
        $data = $request->validate([
            'quality_id' => ['nullable', 'integer', 'exists:grey_qualities,id'],
            'quality_no' => ['nullable', 'string', 'max:40'],
            'tag' => ['nullable', 'string', 'max:40'],
            'season' => ['nullable', 'string', 'max:40'],
            'is_active' => ['nullable', 'boolean'],
            'reed' => ['nullable', 'numeric', 'min:0'],
            'pick' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'total_ends' => ['nullable', 'numeric', 'min:0'],
            'yarn_blend_id' => ['nullable', 'integer', 'exists:yarn_blends,id'],
            'blend_label' => ['nullable', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:80'],
            'quality_name_manual' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'array'],
            'details.*.id' => ['nullable', 'integer'],
            'details.*.nature' => ['nullable', 'string', Rule::in(['WARP', 'WEFT'])],
            'details.*.yarn_count_id' => ['nullable', 'integer', 'exists:yarn_counts,id'],
            'details.*.yarn_thread_id' => ['nullable', 'integer', 'exists:yarn_threads,id'],
            'details.*.yarn_blend_id' => ['nullable', 'integer', 'exists:yarn_blends,id'],
            'details.*.line_name' => ['nullable', 'string', 'max:255'],
            'details.*.ends' => ['nullable', 'numeric', 'min:0'],
            'details.*.picks' => ['nullable', 'numeric', 'min:0'],
            'details.*.calc_count' => ['nullable', 'numeric', 'min:0'],
            'details.*.weight' => ['nullable', 'numeric', 'min:0'],
        ]);

        $quality = DB::transaction(function () use ($data, $nameBuilder) {
            $qualityNo = trim((string) ($data['quality_no'] ?? ''));
            if ($qualityNo === '') {
                $qualityNo = (string) $this->nextQualityNo();
            }

            $autoName = $nameBuilder->build(
                isset($data['reed']) ? (float) $data['reed'] : null,
                isset($data['pick']) ? (float) $data['pick'] : null,
                isset($data['width']) ? (float) $data['width'] : null,
                isset($data['total_ends']) ? (float) $data['total_ends'] : null,
                $data['yarn_blend_id'] ?? null,
                $data['blend_label'] ?? null,
                $data['color'] ?? null,
            );

            $attributes = [
                'quality_no' => $qualityNo,
                'tag' => $data['tag'] ?? null,
                'season' => $data['season'] ?? null,
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'reed' => $data['reed'] ?? null,
                'pick' => $data['pick'] ?? null,
                'width' => $data['width'] ?? null,
                'total_ends' => $data['total_ends'] ?? 0,
                'yarn_blend_id' => $data['yarn_blend_id'] ?? null,
                'blend_label' => $data['blend_label'] ?? null,
                'color' => $data['color'] ?? null,
                'quality_name' => $autoName,
                'quality_name_manual' => $data['quality_name_manual'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => Auth::id(),
            ];

            if (! empty($data['quality_id'])) {
                $quality = GreyQuality::query()->findOrFail($data['quality_id']);
                $quality->update($attributes);
            } else {
                $quality = GreyQuality::query()->create($attributes);
            }

            $this->syncQualityDetails($quality, $data['details'] ?? []);

            return $quality;
        });

        return redirect()
            ->route('erp.grey.master-data', ['tab' => 'master', 'edit' => $quality->id])
            ->with('status', "Grey quality {$quality->quality_no} saved.");
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function syncQualityDetails(GreyQuality $quality, array $rows): void
    {
        $keptIds = [];
        $sort = 0;

        foreach ($rows as $row) {
            $nature = strtoupper(trim((string) ($row['nature'] ?? '')));
            if ($nature === '' && empty($row['yarn_count_id']) && empty($row['line_name'])) {
                continue;
            }

            $attributes = [
                'nature' => $nature !== '' ? $nature : 'WARP',
                'yarn_count_id' => $row['yarn_count_id'] ?? null,
                'yarn_thread_id' => $row['yarn_thread_id'] ?? null,
                'yarn_blend_id' => $row['yarn_blend_id'] ?? null,
                'line_name' => $row['line_name'] ?? null,
                'ends' => $row['ends'] ?? null,
                'picks' => $row['picks'] ?? null,
                'calc_count' => $row['calc_count'] ?? null,
                'weight' => $row['weight'] ?? null,
                'sort_order' => $sort++,
            ];

            if (! empty($row['id'])) {
                $detail = GreyQualityDetail::query()
                    ->where('grey_quality_id', $quality->id)
                    ->find($row['id']);
                if ($detail) {
                    $detail->update($attributes);
                    $keptIds[] = $detail->id;

                    continue;
                }
            }

            $detail = $quality->details()->create($attributes);
            $keptIds[] = $detail->id;
        }

        if ($keptIds !== []) {
            GreyQualityDetail::query()
                ->where('grey_quality_id', $quality->id)
                ->whereNotIn('id', $keptIds)
                ->delete();
        }
    }

    private function storeGodowns(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'godowns' => ['nullable', 'array'],
            'godowns.*.id' => ['nullable', 'integer'],
            'godowns.*.name' => ['nullable', 'string', 'max:120'],
            'godowns.*.is_active' => ['nullable', 'boolean'],
        ]);

        $keptIds = [];
        foreach ($data['godowns'] ?? [] as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $attributes = [
                'name' => $name,
                'module' => 'grey',
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
                ->where('module', 'grey')
                ->whereNotIn('id', $keptIds)
                ->update(['is_active' => false]);
        }

        return redirect()
            ->route('erp.grey.master-data', ['tab' => 'godowns'])
            ->with('status', 'Grey godowns saved.');
    }

    private function nextQualityNo(): int
    {
        $max = GreyQuality::query()
            ->pluck('quality_no')
            ->map(static fn ($no) => is_numeric($no) ? (int) $no : 0)
            ->max();

        return max(100900, (int) $max) + 1;
    }

    private function allowed(string $action): bool
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User) {
            return false;
        }

        $permissions = [
            "grey.master-data.{$action}",
            "grey.master-grey.{$action}",
            "grey.master-godowns.{$action}",
        ];

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}

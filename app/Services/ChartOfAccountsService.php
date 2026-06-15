<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountsService
{
  /** @var array<string, int> */
    private const LEVEL_SEGMENT_WIDTH = [
        'head' => 2,
        'control' => 3,
        'ledger' => 4,
        'sub_ledger' => 5,
    ];

    public function __construct(private readonly string $ledger = Account::LEDGER_GENERAL) {}

    public static function general(): self
    {
        return new self(Account::LEDGER_GENERAL);
    }

    public static function weaving(): self
    {
        return new self(Account::LEDGER_WEAVING);
    }

    public function ledger(): string
    {
        return $this->ledger;
    }

  /**
   * @return array<string, list<\Illuminate\Validation\Rules\Exists|string>>
   */
    public function storeValidationRules(): array
    {
        return [
            'level' => ['required', 'string', Rule::in(['head', 'control', 'ledger', 'sub_ledger'])],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query->where('ledger', $this->ledger)),
            ],
        ];
    }

  /**
   * @return array<string, list<\Illuminate\Validation\Rules\Exists|string>>
   */
    public function updateValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query->where('ledger', $this->ledger)),
            ],
        ];
    }

    public function accountsForListing()
    {
        $levelOrder = "CASE level WHEN 'head' THEN 1 WHEN 'control' THEN 2 WHEN 'ledger' THEN 3 WHEN 'sub_ledger' THEN 4 ELSE 5 END";

        return $this->baseQuery()
            ->with('parent')
            ->orderByRaw($levelOrder)
            ->orderBy('code')
            ->get();
    }

  /**
   * @return array<string, mixed>
   */
    public function parentsJson(): array
    {
        return [
            'control' => $this->baseQuery()->where('level', 'head')->whereNull('parent_id')->orderBy('code')->get(['id', 'code', 'name'])->values(),
            'ledger' => $this->baseQuery()->where('level', 'control')->orderBy('code')->get(['id', 'code', 'name'])->values(),
            'sub_ledger' => $this->baseQuery()->where('level', 'ledger')->orderBy('code')->get(['id', 'code', 'name'])->values(),
        ];
    }

  /**
   * @param  array<string, mixed>  $validated
   */
    public function create(array $validated): Account
    {
        $level = $validated['level'];
        $parentId = $validated['parent_id'] ?? null;
        $parent = $this->resolveParentForLevel($level, $parentId);

        $local = $this->nextLocalSegmentNumber($level, $parent);
        $code = $this->buildHierarchicalCode($level, $parent, $local);

        return Account::query()->create([
            'ledger' => $this->ledger,
            'level' => $level,
            'code' => $code,
            'name' => $validated['name'],
            'parent_id' => $parentId,
            'is_active' => true,
        ]);
    }

  /**
   * @param  array<string, mixed>  $validated
   */
    public function update(Account $account, array $validated, Request $request): Account
    {
        $this->assertLedgerAccount($account);

        $level = $account->level;
        $parentId = $validated['parent_id'] ?? $account->parent_id;
        $parent = null;

        if ($level === 'head') {
            $parentId = null;
        } elseif ($parentId === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'parent_id' => 'Select a parent account.',
            ]);
        } else {
            $parent = $this->baseQuery()->findOrFail($parentId);
            $expectedParentLevel = $this->expectedParentLevel($level);
            if ($expectedParentLevel === null || $parent->level !== $expectedParentLevel) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parent_id' => 'Parent account does not match this level.',
                ]);
            }
        }

        $updates = [
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($level !== 'head' && (int) $parentId !== (int) $account->parent_id) {
            $oldParent = $account->parent;
            $suffix = (string) $account->code;
            if ($oldParent !== null && str_starts_with($suffix, (string) $oldParent->code)) {
                $suffix = substr($suffix, strlen((string) $oldParent->code));
            }
            $local = ctype_digit($suffix) && $suffix !== '' ? (int) $suffix : $this->nextLocalSegmentNumber($level, $parent);
            $newCode = $this->buildHierarchicalCode($level, $parent, $local);

            if ($this->baseQuery()->where('code', $newCode)->where('id', '!=', $account->id)->exists()) {
                $local = $this->nextLocalSegmentNumber($level, $parent);
                $newCode = $this->buildHierarchicalCode($level, $parent, $local);
            }

            $updates['parent_id'] = $parentId;
            $updates['code'] = $newCode;
        } elseif ($level !== 'head') {
            $updates['parent_id'] = $parentId;
        }

        $account->update($updates);

        return $account->fresh();
    }

    public function assertLedgerAccount(Account $account): void
    {
        abort_unless($account->ledger === $this->ledger, 404);
    }

    private function baseQuery()
    {
        return Account::query()->where('ledger', $this->ledger);
    }

    private function resolveParentForLevel(string $level, ?int $parentId): ?Account
    {
        if ($level === 'head') {
            return null;
        }

        if ($parentId === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'parent_id' => 'Select a parent account.',
            ]);
        }

        $parent = $this->baseQuery()->findOrFail($parentId);
        $expectedParentLevel = $this->expectedParentLevel($level);
        if ($expectedParentLevel === null || $parent->level !== $expectedParentLevel) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'parent_id' => 'Parent account does not match this level.',
            ]);
        }

        return $parent;
    }

    private function expectedParentLevel(string $level): ?string
    {
        return match ($level) {
            'control' => 'head',
            'ledger' => 'control',
            'sub_ledger' => 'ledger',
            default => null,
        };
    }

    private function nextLocalSegmentNumber(string $level, ?Account $parent): int
    {
        if ($level === 'head') {
            $max = 0;
            foreach ($this->baseQuery()->where('level', 'head')->whereNull('parent_id')->pluck('code') as $c) {
                if ($c !== null && $c !== '' && ctype_digit((string) $c)) {
                    $max = max($max, (int) $c);
                }
            }

            return $max + 1;
        }

        if ($parent === null) {
            return 1;
        }

        $prefix = (string) $parent->code;
        $prefixLen = strlen($prefix);
        $max = 0;

        foreach ($this->baseQuery()->where('parent_id', $parent->id)->pluck('code') as $c) {
            $c = (string) $c;
            if ($prefixLen > 0 && ! str_starts_with($c, $prefix)) {
                continue;
            }
            $suffix = substr($c, $prefixLen);
            if ($suffix === '' || ! ctype_digit($suffix)) {
                continue;
            }
            $max = max($max, (int) $suffix);
        }

        return $max + 1;
    }

    private function buildHierarchicalCode(string $level, ?Account $parent, int $localNumber): string
    {
        $segment = str_pad((string) $localNumber, $this->segmentWidthForLevel($level), '0', STR_PAD_LEFT);

        if ($level === 'head') {
            return $segment;
        }

        if ($parent === null) {
            throw new \InvalidArgumentException('Parent account is required to build a non-head code.');
        }

        return $parent->code . $segment;
    }

    private function segmentWidthForLevel(string $level): int
    {
        return self::LEVEL_SEGMENT_WIDTH[$level] ?? 2;
    }
}

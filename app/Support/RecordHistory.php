<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RecordHistory
{
    public const PER_PAGE_DEFAULT = 15;

    public static function perPage(Request $request): int
    {
        $perPage = (int) $request->query('history_per_page', self::PER_PAGE_DEFAULT);

        return max(5, min(50, $perPage));
    }

    public static function applyDateRange(Builder $query, Request $request, string $dateColumn): Builder
    {
        $from = ErpDate::toStorage($request->query('history_from'));
        $to = ErpDate::toStorage($request->query('history_to'));

        if ($from !== null) {
            $query->whereDate($dateColumn, '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate($dateColumn, '<=', $to);
        }

        return $query;
    }

    /**
     * @return array{recordsHistory: LengthAwarePaginator, recordsHistoryGrouped: Collection<int, Collection<int, mixed>>}
     */
    public static function build(Request $request, Builder $query, string $dateColumn): array
    {
        $paginator = self::applyDateRange($query, $request, $dateColumn)
            ->paginate(self::perPage($request))
            ->withQueryString();

        return [
            'recordsHistory' => $paginator,
            'recordsHistoryGrouped' => self::groupByDate($paginator->getCollection(), $dateColumn),
        ];
    }

    public static function groupByDate(Collection $records, string $dateAttribute): Collection
    {
        return $records->groupBy(function ($record) use ($dateAttribute) {
            $label = ErpDate::display($record->{$dateAttribute});

            return $label !== '' ? $label : '—';
        });
    }
}

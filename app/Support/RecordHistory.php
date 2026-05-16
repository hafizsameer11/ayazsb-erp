<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RecordHistory
{
    public static function selectedDateStorage(Request $request): string
    {
        $parsed = ErpDate::toStorage($request->query('history_date'));

        return $parsed ?? now()->format(ErpDate::STORAGE_FORMAT);
    }

    public static function selectedDateDisplay(Request $request): string
    {
        return ErpDate::display(self::selectedDateStorage($request));
    }

    public static function applySelectedDate(Builder $query, Request $request, string $dateColumn): Builder
    {
        return $query->whereDate($dateColumn, self::selectedDateStorage($request));
    }

    /**
     * @return array{
     *     historyDate: string,
     *     historyDateStorage: string,
     *     recordsForDay: Collection<int, mixed>,
     *     historyNav: array{prev: string, next: string, today: string}
     * }
     */
    public static function buildForDay(
        Request $request,
        Builder $query,
        string $dateColumn,
        string $routeName,
        array $routeParameters = [],
    ): array {
        $storage = self::selectedDateStorage($request);
        $display = ErpDate::display($storage);
        $current = Carbon::parse($storage)->startOfDay();

        $records = self::applySelectedDate($query, $request, $dateColumn)->get();

        $baseQuery = array_filter(
            $request->only(['edit']),
            static fn ($value) => $value !== null && $value !== '',
        );

        $dayParam = fn (Carbon $day): array => array_merge($baseQuery, [
            'history_date' => $day->format(ErpDate::DISPLAY_FORMAT),
        ]);

        return [
            'historyDate' => $display,
            'historyDateStorage' => $storage,
            'recordsForDay' => $records,
            'historyNav' => [
                'prev' => route($routeName, array_merge($routeParameters, $dayParam($current->copy()->subDay()))),
                'next' => route($routeName, array_merge($routeParameters, $dayParam($current->copy()->addDay()))),
                'today' => route($routeName, array_merge($routeParameters, $dayParam(now()->startOfDay()))),
            ],
        ];
    }

    public static function editUrl(Request $request, string $routeName, array $routeParameters, int $recordId): string
    {
        $query = array_filter([
            'history_date' => $request->query('history_date') ?: self::selectedDateDisplay($request),
            'edit' => $recordId,
        ], static fn ($value) => $value !== null && $value !== '');

        $url = route($routeName, $routeParameters);

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    /**
     * @return array<string, mixed>
     */
    public static function historyQuery(Request $request): array
    {
        return array_filter([
            'history_date' => $request->query('history_date') ?: self::selectedDateDisplay($request),
        ], static fn ($value) => $value !== null && $value !== '');
    }
}

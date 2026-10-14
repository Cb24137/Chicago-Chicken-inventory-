<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

// Groups records by month for the column charts on the dashboard and reports
class MonthlyTotals
{
    // One point per month from $start to $end (at most the last 24 months)
    public static function series(Collection $rows, string $dateColumn, callable $total, Carbon $start, Carbon $end): array
    {
        $first = $start->copy()->startOfMonth()->max($end->copy()->startOfMonth()->subMonths(23));
        $last = $end->copy()->startOfMonth();
        $labelFormat = $first->year === $last->year ? 'M' : 'M y';

        $byMonth = $rows->groupBy(fn ($row) => Carbon::parse($row->{$dateColumn})->format('Y-m'));
        $points = [];

        for ($month = $first->copy(); $month->lte($last); $month->addMonth()) {
            $points[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format($labelFormat),
                'full_label' => $month->format('F Y'),
                'value' => $total($byMonth->get($month->format('Y-m'), collect())),
            ];
        }

        return $points;
    }
}

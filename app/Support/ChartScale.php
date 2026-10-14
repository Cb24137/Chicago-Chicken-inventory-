<?php

namespace App\Support;

// Value axis for the column and line charts: rounds the top of the axis up
// to a clean step (1, 2, 2.5 or 5 x 10^n) so the gridlines read naturally.
class ChartScale
{
    // ['step' => float, 'max' => float, 'ticks' => int]
    public static function for(float $largest, int $decimals = 0): array
    {
        $step = 1;

        if ($largest > 0) {
            $rough = $largest / 4;
            $magnitude = 10 ** floor(log10($rough));

            foreach ([1, 2, 2.5, 5, 10] as $multiplier) {
                if ($multiplier * $magnitude >= $rough) {
                    $step = $multiplier * $magnitude;
                    break;
                }
            }

            if ($decimals === 0) {
                $step = max(1, ceil($step));
            }
        }

        $max = max($step, $step * ceil($largest / $step));

        return ['step' => $step, 'max' => $max, 'ticks' => (int) round($max / $step)];
    }

    // Compact axis label: 1500 => "1.5K", 2.5 => "2.5"
    public static function short(float $value): string
    {
        return $value >= 1000
            ? rtrim(rtrim(number_format($value / 1000, 1, '.', ''), '0'), '.') . 'K'
            : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}

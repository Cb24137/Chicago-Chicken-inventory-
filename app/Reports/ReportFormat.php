<?php

namespace App\Reports;

use DateTimeInterface;

// Formats report table cells the same way on screen and in the PDF.
// Column formats: text, code (e.g. PO numbers), number, money, date.
class ReportFormat
{
    public static function cell(mixed $value, string $format): string
    {
        return match (true) {
            $value === null || $value === '' => '—',
            $value instanceof DateTimeInterface => $value->format('d M Y'),
            $format === 'money' => number_format((float) $value, 2),
            $format === 'number' => rtrim(rtrim(number_format((float) $value, 2), '0'), '.'),
            default => (string) $value,
        };
    }

    // Numbers and money are right-aligned
    public static function isNumeric(string $format): bool
    {
        return in_array($format, ['number', 'money'], true);
    }

    // Column CSS class: numbers right-aligned, codes and dates kept on one line
    public static function cellClass(string $format, bool $forPdf = false): string
    {
        return match (true) {
            self::isNumeric($format) => $forPdf ? 'num' : 'is-num',
            in_array($format, ['code', 'date'], true) => $forPdf ? 'nowrap' : 'ccc-nowrap',
            default => '',
        };
    }

    // True when a chart has at least one non-zero value to draw
    public static function chartHasData(array $chart): bool
    {
        $values = array_column($chart['points'] ?? $chart['items'] ?? [], 'value');

        return array_sum(array_map('floatval', $values)) > 0;
    }
}

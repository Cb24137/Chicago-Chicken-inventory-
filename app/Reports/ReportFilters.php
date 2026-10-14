<?php

namespace App\Reports;

use Illuminate\Support\Carbon;

// The validated filters for one report request
class ReportFilters
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public array $values = [],
    ) {
    }

    public function get(string $key): mixed
    {
        $value = $this->values[$key] ?? null;

        return $value === '' ? null : $value;
    }

    public function period(): string
    {
        return $this->from->format('d M Y') . ' to ' . $this->to->format('d M Y');
    }

    // The query string that recreates these filters (for links and exports)
    public function query(): array
    {
        return array_filter([
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
            ...$this->values,
        ], fn ($value) => $value !== null && $value !== '');
    }
}

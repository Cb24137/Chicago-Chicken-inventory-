<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

// A reporting period (a preset such as "This month", or a custom range)
// and the period it is compared with. Comparisons are like-for-like:
// 1-14 Oct is compared with 1-14 Sep, never with the whole of September.
class ReportingPeriod
{
    // Preset key => label
    public const PRESETS = [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'last_3_months' => 'Last 3 months',
        'last_6_months' => 'Last 6 months',
        'this_year' => 'This year',
    ];

    public function __construct(
        public readonly string $key,
        public readonly Carbon $from,
        public readonly Carbon $to,
    ) {
    }

    public static function preset(string $key): self
    {
        $today = today();

        [$from, $to] = match ($key) {
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [$today->copy()->subMonthsNoOverflow(2)->startOfMonth(), $today->copy()],
            'last_6_months' => [$today->copy()->subMonthsNoOverflow(5)->startOfMonth(), $today->copy()],
            'this_year' => [$today->copy()->startOfYear(), $today->copy()],
            default => [$today->copy()->startOfMonth(), $today->copy()],
        };

        return new self(array_key_exists($key, self::PRESETS) ? $key : 'this_month', $from->startOfDay(), $to->endOfDay());
    }

    // A date range; recognised as a preset when it matches one exactly
    public static function between(Carbon $from, Carbon $to): self
    {
        foreach (array_keys(self::PRESETS) as $key) {
            $preset = self::preset($key);

            if ($preset->from->isSameDay($from) && $preset->to->isSameDay($to)) {
                return $preset;
            }
        }

        return new self('custom', $from->copy()->startOfDay(), $to->copy()->endOfDay());
    }

    // ?period=<preset> or ?period=custom&from=YYYY-MM-DD&to=YYYY-MM-DD
    public static function fromRequest(Request $request, string $default = 'this_month'): self
    {
        $data = $request->validate([
            'period' => ['nullable', Rule::in([...array_keys(self::PRESETS), 'custom'])],
            'from' => ['nullable', 'required_if:period,custom', 'date_format:Y-m-d'],
            'to' => ['nullable', 'required_if:period,custom', 'date_format:Y-m-d', 'after_or_equal:from'],
        ], [
            'from.required_if' => 'Choose a start date for the custom range.',
            'to.required_if' => 'Choose an end date for the custom range.',
        ], [
            'from' => 'start date',
            'to' => 'end date',
        ]);

        if (($data['period'] ?? null) === 'custom') {
            return new self('custom', Carbon::parse($data['from'])->startOfDay(), Carbon::parse($data['to'])->endOfDay());
        }

        return self::preset($data['period'] ?? $default);
    }

    // The period this one is compared with
    public function previous(): self
    {
        [$from, $to] = match ($this->key) {
            'this_month' => [$this->from->copy()->subMonthNoOverflow(), $this->to->copy()->subMonthNoOverflow()],
            'last_month' => [$this->from->copy()->subMonthNoOverflow()->startOfMonth(), $this->from->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [$this->from->copy()->subMonthsNoOverflow(3), $this->to->copy()->subMonthsNoOverflow(3)],
            'last_6_months' => [$this->from->copy()->subMonthsNoOverflow(6), $this->to->copy()->subMonthsNoOverflow(6)],
            'this_year' => [$this->from->copy()->subYearNoOverflow(), $this->to->copy()->subYearNoOverflow()],
            // A custom range: the same number of days just before it
            default => [$this->from->copy()->subDays($this->days()), $this->from->copy()->subDay()],
        };

        return new self('previous', $from->startOfDay(), $to->endOfDay());
    }

    public function days(): int
    {
        return (int) $this->from->copy()->startOfDay()->diffInDays($this->to->copy()->startOfDay()) + 1;
    }

    // e.g. "1–14 Oct 2026", "1 Aug – 14 Oct 2026", "1 Nov 2025 – 14 Oct 2026"
    public function label(): string
    {
        return match (true) {
            $this->from->isSameDay($this->to) => $this->to->format('j M Y'),
            $this->from->isSameMonth($this->to) => $this->from->format('j') . '–' . $this->to->format('j M Y'),
            $this->from->isSameYear($this->to) => $this->from->format('j M') . ' – ' . $this->to->format('j M Y'),
            default => $this->from->format('j M Y') . ' – ' . $this->to->format('j M Y'),
        };
    }

    public function name(): string
    {
        return self::PRESETS[$this->key] ?? 'Custom range';
    }

    // Query string that reopens this period (used by links and exports)
    public function query(): array
    {
        return $this->key === 'custom'
            ? ['period' => 'custom', 'from' => $this->from->toDateString(), 'to' => $this->to->toDateString()]
            : ['period' => $this->key];
    }

    // from/to for the report pages
    public function reportQuery(): array
    {
        return ['from' => $this->from->toDateString(), 'to' => $this->to->toDateString()];
    }
}

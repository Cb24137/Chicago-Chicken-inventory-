<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Reports\InventoryReport;
use App\Reports\OperationalSummaryReport;
use App\Reports\PurchaseReport;
use App\Reports\ReportFilters;
use App\Reports\StorageReport;
use App\Reports\SupplierReport;
use App\Reports\WasteReport;
use App\Support\ReportingPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Reports & Analytics: on-screen reports with CSV, PDF and print output.
// Every figure is calculated from MySQL when the report is requested.
class ReportController extends Controller
{
    // Report key => tab label
    public const REPORTS = [
        'inventory' => 'Inventory',
        'purchases' => 'Purchases',
        'suppliers' => 'Suppliers',
        'storage' => 'Storage',
        'waste' => 'Waste & Expiry',
        'summary' => 'Operational Summary',
    ];

    // Reports staff may open; admins can open all of them
    public const STAFF_REPORTS = ['inventory', 'storage', 'waste'];

    private const BUILDERS = [
        'inventory' => InventoryReport::class,
        'purchases' => PurchaseReport::class,
        'suppliers' => SupplierReport::class,
        'storage' => StorageReport::class,
        'waste' => WasteReport::class,
        'summary' => OperationalSummaryReport::class,
    ];

    // Filters each report accepts (besides the from/to dates)
    private const FILTERS = [
        'inventory' => ['category', 'stock_status'],
        'purchases' => ['supplier_id', 'status'],
        'suppliers' => [],
        'storage' => ['type', 'location_status'],
        'waste' => ['reason'],
        'summary' => [],
    ];

    public function index(Request $request)
    {
        return redirect()->route('reports.show', array_key_first($this->allowedReports($request)));
    }

    public function show(Request $request, string $report)
    {
        $this->authorizeReport($report);
        $filters = $this->filters($request, $report);

        return view('reports.show', [
            'reportKey' => $report,
            'reports' => $this->allowedReports($request),
            'report' => $this->build($report, $filters),
            'filters' => $filters,
            'filterSummary' => $this->filterSummary($filters),
            'presets' => $this->presets(),
            'suppliers' => $report === 'purchases' ? Supplier::orderBy('name')->get(['id', 'name']) : collect(),
            'generatedAt' => now(),
        ]);
    }

    public function export(Request $request, string $report, string $format)
    {
        $this->authorizeReport($report);
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $filters = $this->filters($request, $report);
        $data = $this->build($report, $filters);
        $meta = [
            'generatedAt' => now(),
            'generatedBy' => $request->user()->name,
            'filterSummary' => $this->filterSummary($filters),
        ];
        $filename = "chicago-chicken-city-{$report}-report-" . now()->format('Y-m-d') . ".{$format}";

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', ['report' => $data] + $meta)
                ->setPaper('a4', 'landscape')
                // Embed only the characters used, not the whole font file
                ->setOption('isFontSubsettingEnabled', true);

            $this->stampPageNumbers($pdf);

            return $pdf->download($filename);
        }

        return response()->streamDownload(
            fn () => $this->writeCsv($data, $meta),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function authorizeReport(string $report): void
    {
        abort_unless(array_key_exists($report, self::REPORTS), 404);

        Gate::authorize('view-report', $report);
    }

    private function allowedReports(Request $request): array
    {
        return array_filter(
            self::REPORTS,
            fn ($key) => $request->user()->can('view-report', $key),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function build(string $report, ReportFilters $filters): array
    {
        return app(self::BUILDERS[$report])->build($filters);
    }

    // Validated filters; the period defaults to this year
    private function filters(Request $request, string $report): ReportFilters
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'category' => ['nullable', Rule::in(InventoryItem::CATEGORIES)],
            'stock_status' => ['nullable', Rule::in(array_keys(InventoryItem::STOCK_STATUSES))],
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'status' => ['nullable', Rule::in(array_keys(Purchase::STATUSES))],
            'type' => ['nullable', Rule::in(StorageReport::TYPES)],
            'location_status' => ['nullable', Rule::in(['active', 'inactive'])],
            'reason' => ['nullable', Rule::in(array_keys(WasteReport::REASONS))],
        ], [], [
            'from' => 'start date',
            'to' => 'end date',
        ]);

        return new ReportFilters(
            isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->startOfYear(),
            isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay(),
            Arr::only($data, self::FILTERS[$report]),
        );
    }

    // The filters in use, in words, for the report header and exports
    private function filterSummary(ReportFilters $filters): array
    {
        $supplierId = $filters->get('supplier_id');

        return array_filter([
            'Category' => $filters->get('category'),
            'Stock status' => InventoryItem::STOCK_STATUSES[(string) $filters->get('stock_status')] ?? null,
            'Supplier' => $supplierId ? Supplier::find($supplierId)?->name : null,
            'Status' => Purchase::STATUSES[(string) $filters->get('status')] ?? null,
            'Type' => $filters->get('type'),
            'Location status' => ['active' => 'Active', 'inactive' => 'Inactive'][(string) $filters->get('location_status')] ?? null,
            'Reason' => WasteReport::REASONS[(string) $filters->get('reason')] ?? null,
        ]);
    }

    // Quick date ranges shown above the filters (the same as on the dashboard)
    private function presets(): array
    {
        return collect(ReportingPeriod::PRESETS)
            ->mapWithKeys(function ($label, $key) {
                $period = ReportingPeriod::preset($key);

                return [$label => [$period->from, $period->to]];
            })
            ->all();
    }

    // "Page X of Y" at the bottom right of every PDF page. Dompdf only knows
    // the page count after rendering, so it is drawn onto the finished pages.
    private function stampPageNumbers(PdfDocument $pdf): void
    {
        $pdf->render();

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $size = 6; // points, the same as the footer text
        $pages = $canvas->get_page_count();
        $width = $dompdf->getFontMetrics()->getTextWidth("Page {$pages} of {$pages}", $font, $size);

        // Lines up with the footer in reports/pdf.blade.php (32px side margin)
        $canvas->page_text(
            $canvas->get_width() - 24 - $width,
            $canvas->get_height() - 22,
            'Page {PAGE_NUM} of {PAGE_COUNT}',
            $font,
            $size,
            [0.478, 0.388, 0.322]
        );
    }

    private function writeCsv(array $report, array $meta): void
    {
        $out = fopen('php://output', 'w');

        // Byte order mark so Excel reads characters such as "°" and "·" correctly
        fwrite($out, "\xEF\xBB\xBF");

        $write = fn (array $row) => fputcsv($out, $row, ',', '"', '');

        $write(['Chicago Chicken City']);
        $write([$report['title']]);
        $write(['Period', $report['period']]);
        $write(['Generated', $meta['generatedAt']->format('d M Y, g:i A') . ' by ' . $this->csvText($meta['generatedBy'])]);

        if ($meta['filterSummary']) {
            $write(['Filters', $this->csvText(collect($meta['filterSummary'])->map(fn ($value, $name) => "{$name}: {$value}")->implode('; '))]);
        }

        $write([]);
        $write(['Summary']);

        foreach ($report['summary'] as $item) {
            $write([$item['label'], $item['value']]);
        }

        foreach ($report['tables'] as $table) {
            $formats = array_values($table['columns']);

            $write([]);
            $write([$table['title']]);
            $write(array_keys($table['columns']));

            foreach ($table['rows'] as $row) {
                $write(array_map(fn ($cell, $format) => $this->csvCell($cell, $format), $row, $formats));
            }
        }

        fclose($out);
    }

    private function csvCell(mixed $value, string $format): string
    {
        return match (true) {
            $value === null => '',
            $value instanceof DateTimeInterface => $value->format('Y-m-d'),
            $format === 'money' => number_format((float) $value, 2, '.', ''),
            $format === 'number' => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.'),
            default => $this->csvText((string) $value),
        };
    }

    // Text that starts like a formula is escaped, so spreadsheet apps
    // never run a value typed into the system as a formula
    private function csvText(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'{$value}" : $value;
    }
}

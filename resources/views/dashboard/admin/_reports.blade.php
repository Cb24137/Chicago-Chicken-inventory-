{{-- Section 6: reports, opened or exported for the selected period --}}
<section class="ccc-section" aria-labelledby="reports-title">
    <div class="ccc-section-head">
        <h2 id="reports-title" class="ccc-section-title">Reports &amp; analytics</h2>
        <p class="ccc-section-text">Preview, export or print · dated reports use {{ $period->label() }}</p>
    </div>

    <div class="ccc-report-cards">
        @foreach ($reportCards as $card)
            @php $query = ['report' => $card['key']] + ($card['dated'] ? $period->reportQuery() : []); @endphp

            <article class="ccc-report-card" aria-labelledby="report-card-{{ $card['key'] }}">
                <div class="ccc-report-card-head">
                    <span class="ccc-report-card-icon"><x-icon :name="$card['icon']" /></span>
                    <div>
                        <h3 id="report-card-{{ $card['key'] }}">{{ $card['title'] }}</h3>
                        <p>{{ $card['dated'] ? $period->label() : 'Current state' }}</p>
                    </div>
                </div>

                <p class="ccc-report-card-text">{{ $card['description'] }}</p>
                <p class="ccc-report-card-figure">{{ $card['figure'] }}</p>

                <div class="ccc-report-card-actions">
                    <a href="{{ route('reports.show', $query) }}" class="ccc-btn ccc-btn-secondary ccc-btn-sm">
                        <x-icon name="eye" /> Preview
                    </a>
                    <a href="{{ route('reports.export', $query + ['format' => 'pdf']) }}" class="ccc-btn ccc-btn-soft ccc-btn-sm" data-loading-text="Preparing…">
                        <x-icon name="file" /> <span data-label>PDF</span>
                        <span class="ccc-visually-hidden">of the {{ $card['title'] }}</span>
                    </a>
                    <a href="{{ route('reports.export', $query + ['format' => 'csv']) }}" class="ccc-btn ccc-btn-soft ccc-btn-sm" data-loading-text="Preparing…">
                        <x-icon name="download" /> <span data-label>CSV</span>
                        <span class="ccc-visually-hidden">of the {{ $card['title'] }}</span>
                    </a>
                </div>
            </article>
        @endforeach
    </div>
</section>

{{-- Section 4: management insights (each finding states its numbers and source) --}}
<section class="ccc-card ccc-span-7" aria-labelledby="insights-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="insights-title" class="ccc-card-title"><x-icon name="lightbulb" /> Management insights</h2>
            <p class="ccc-card-subtitle">Calculated from the data for {{ $period->label() }}, compared with {{ $previous->label() }}</p>
        </div>
    </div>

    <div class="ccc-card-body">
        <ul class="ccc-insights">
            @foreach ($insights as $insight)
                <li class="ccc-insight is-{{ $insight['tone'] }}">
                    <span class="ccc-insight-icon"><x-icon :name="$insight['icon']" /></span>
                    <div>
                        <h3 class="ccc-insight-title">{{ $insight['title'] }}</h3>
                        <p class="ccc-insight-text">{{ $insight['text'] }}</p>
                        <p class="ccc-insight-basis">
                            Based on: {{ $insight['basis'] }}
                            @if (! empty($insight['link']))
                                · <a href="{{ $insight['link'] }}">{{ $insight['linkLabel'] }}</a>
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>

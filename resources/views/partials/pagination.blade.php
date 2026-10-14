{{-- Shared pagination links for every module table --}}
@if ($paginator->hasPages())
    <nav class="ccc-pagination" aria-label="Pagination">
        @if (method_exists($paginator, 'total'))
            <p class="ccc-pagination-info">
                Showing <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
                of <strong>{{ $paginator->total() }}</strong>
            </p>
        @endif

        <ul class="ccc-pagination-links">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="ccc-page-link is-disabled" aria-disabled="true">‹ Previous</span>
                @else
                    <a class="ccc-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Previous</a>
                @endif
            </li>

            @isset($elements)
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span class="ccc-page-link is-gap">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <span class="ccc-page-link is-current" aria-current="page">{{ $page }}</span>
                                @else
                                    <a class="ccc-page-link" href="{{ $url }}">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach
            @endisset

            <li>
                @if ($paginator->hasMorePages())
                    <a class="ccc-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next ›</a>
                @else
                    <span class="ccc-page-link is-disabled" aria-disabled="true">Next ›</span>
                @endif
            </li>
        </ul>
    </nav>
@endif

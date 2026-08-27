@if ($paginator->hasPages())
    <nav class="site-pagination" role="navigation" aria-label="Pagination">
        <div class="site-pagination-summary">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>
        <div class="site-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="pagination-button disabled" aria-disabled="true">&larr; Previous</span>
            @else
                <a class="pagination-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; Previous</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-ellipsis">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-button" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rarr;</a>
            @else
                <span class="pagination-button disabled" aria-disabled="true">Next &rarr;</span>
            @endif
        </div>
    </nav>
@endif

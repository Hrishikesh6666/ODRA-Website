@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="portal-pagination">
        <div class="pagination-info">
            Showing <span class="font-medium">{{ $paginator->firstItem() }}</span> to <span class="font-medium">{{ $paginator->lastItem() }}</span> of <span class="font-medium">{{ $paginator->total() }}</span> results
        </div>

        <div class="pagination-buttons">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true">
                    &laquo; Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev">
                    &laquo; Previous
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-dots">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next">
                    Next &raquo;
                </a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true">
                    Next &raquo;
                </span>
            @endif
        </div>
    </nav>
@elseif ($paginator->total() > 0)
    <div class="portal-pagination" style="justify-content: flex-start;">
        <div class="pagination-info">
            Showing <span class="font-medium">1</span> to <span class="font-medium">{{ $paginator->total() }}</span> of <span class="font-medium">{{ $paginator->total() }}</span> results
        </div>
    </div>
@endif

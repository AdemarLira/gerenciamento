@if ($paginator->hasPages())
<nav class="pagination" aria-label="Paginação">
    <span class="muted small">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
    </span>
    <div class="pages">
        @if ($paginator->onFirstPage())
            <span class="page disabled"><x-icon name="chevron-left" size="sm"/></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior"><x-icon name="chevron-left" size="sm"/></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page disabled">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Próxima"><x-icon name="chevron-right" size="sm"/></a>
        @else
            <span class="page disabled"><x-icon name="chevron-right" size="sm"/></span>
        @endif
    </div>
</nav>
@endif

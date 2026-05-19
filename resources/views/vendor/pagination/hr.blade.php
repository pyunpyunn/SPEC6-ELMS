@php $anchor = $anchor ?? ''; @endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="pagination-nav">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">&lsaquo; Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}{{ $anchor ? '#'.$anchor : '' }}" rel="prev" aria-label="{{ __('pagination.previous') }}">&lsaquo; Previous</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}{{ $anchor ? '#'.$anchor : '' }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}{{ $anchor ? '#'.$anchor : '' }}" rel="next" aria-label="{{ __('pagination.next') }}">Next &rsaquo;</a>
        @else
            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">Next &rsaquo;</span>
        @endif
    </nav>
@endif

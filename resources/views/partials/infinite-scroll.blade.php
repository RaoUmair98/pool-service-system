{{-- Invisible sentinel that triggers the next page load. Expects $paginator. --}}
<div class="infinite-scroll-status" data-infinite-status hidden
     data-shown="{{ $paginator->lastItem() ?? 0 }}"
     data-total="{{ $paginator->total() }}">
    <span data-infinite-shown>{{ $paginator->lastItem() ?? 0 }}</span>
    <span data-infinite-total>{{ $paginator->total() }}</span>
    <span data-infinite-loader hidden></span>
    <span data-infinite-end hidden></span>
</div>
<div class="infinite-scroll-sentinel" data-infinite-sentinel
     data-next-url="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '' }}"
     @unless ($paginator->hasMorePages()) hidden @endunless
     aria-hidden="true"></div>

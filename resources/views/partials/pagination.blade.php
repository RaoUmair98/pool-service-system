{{--
    Footer under every list: how many rows are showing, the page size box and the page numbers.
    Expects $paginator. $sizes = false hides the page size box (lists that are refreshed in place, small dashboard tables).
--}}
@php
    $sizes = $sizes ?? true;
    $pageSizes = collect(\App\Http\Controllers\Controller::PAGE_SIZES)->push($paginator->perPage())->unique()->sort()->values();
@endphp

<div class="pager-bar d-print-none">
    <div class="pager-info">
        <i class="mdi mdi-format-list-bulleted"></i>
        @if ($paginator->total())
            Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong>
            of <strong>{{ $paginator->total() }}</strong>
        @else
            Nothing to show
        @endif
    </div>

    @if ($sizes && $paginator->total() > $pageSizes->first())
        <form method="GET" class="pager-size">
            {{-- Keep the search and filters; go back to page 1 because the page count changes. --}}
            @foreach (request()->except(['page', 'per_page']) as $key => $value)
                @if (is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="per_page_{{ $paginator->getPageName() }}">Show</label>
            <select name="per_page" id="per_page_{{ $paginator->getPageName() }}" class="pager-select" onchange="this.form.submit()">
                @foreach ($pageSizes as $size)
                    <option value="{{ $size }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
                @endforeach
            </select>
            <span>per page</span>
        </form>
    @endif

    <div class="pager-links">
        @if ($paginator->hasPages())
            {{ $paginator->links('pagination::bootstrap-4') }}
        @else
            <span class="pager-single">Page 1 of 1</span>
        @endif
    </div>
</div>

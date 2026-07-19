@php
    /*
     * Pagination partial — Task 12.2
     * Receives: $paginator (LengthAwarePaginator instance)
     * Shows:
     *   - "Menampilkan X-Y dari Z data"
     *   - Per-page selector (15/30/50) that submits GET preserving all other params
     *   - Page links (prev, numbered pages with window, next)
     *   All links preserve existing query params (search, filter, sort, dir, per_page).
     */
    $total       = $paginator->total();
    $perPage     = $paginator->perPage();
    $currentPage = $paginator->currentPage();
    $lastPage    = $paginator->lastPage();
    $from        = $paginator->firstItem() ?? 0;
    $to          = $paginator->lastItem()  ?? 0;

    // Build a base query array that preserves everything except 'page'
    $queryBase = array_filter(request()->except('page'), fn($v) => $v !== '' && $v !== null);

    // Helper: build URL for a given page number
    $pageUrl = fn(int $p) => request()->fullUrlWithQuery(array_merge($queryBase, ['page' => $p]));

    // Window: show 2 pages around current page (at most 5 links total)
    $window = 2;
    $start  = max(1, $currentPage - $window);
    $end    = min($lastPage, $currentPage + $window);
@endphp

@if($total > 0)
<div class="pagination-bar">
    {{-- Left: info + per-page selector --}}
    <div class="pagination-info">
        <span class="muted">
            Menampilkan <strong>{{ $from }}&ndash;{{ $to }}</strong> dari <strong>{{ $total }}</strong> data
        </span>
        <form method="GET" class="per-page-form" id="per-page-form-{{ $paginator->getPageName() }}">
            {{-- Preserve all existing query params except per_page and page --}}
            @foreach(array_filter(request()->except(['per_page','page']), fn($v) => $v !== '' && $v !== null) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="per_page_{{ $paginator->getPageName() }}" class="muted" style="font-size:13px;">Per halaman:</label>
            <select
                name="per_page"
                id="per_page_{{ $paginator->getPageName() }}"
                class="input per-page-select"
                onchange="this.form.submit()"
            >
                @foreach([15, 30, 50] as $opt)
                    <option value="{{ $opt }}" {{ (int)$perPage === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Right: page navigation links --}}
    @if($lastPage > 1)
    <nav class="pagination-nav" aria-label="Navigasi halaman">
        {{-- Prev --}}
        @if($currentPage > 1)
            <a href="{{ $pageUrl($currentPage - 1) }}" class="page-btn" aria-label="Halaman sebelumnya">&lsaquo;</a>
        @else
            <span class="page-btn page-btn-disabled" aria-disabled="true">&lsaquo;</span>
        @endif

        {{-- First page + ellipsis --}}
        @if($start > 1)
            <a href="{{ $pageUrl(1) }}" class="page-btn">1</a>
            @if($start > 2)
                <span class="page-ellipsis">&hellip;</span>
            @endif
        @endif

        {{-- Numbered pages around current --}}
        @for($p = $start; $p <= $end; $p++)
            @if($p === $currentPage)
                <span class="page-btn page-btn-active" aria-current="page">{{ $p }}</span>
            @else
                <a href="{{ $pageUrl($p) }}" class="page-btn">{{ $p }}</a>
            @endif
        @endfor

        {{-- Ellipsis + last page --}}
        @if($end < $lastPage)
            @if($end < $lastPage - 1)
                <span class="page-ellipsis">&hellip;</span>
            @endif
            <a href="{{ $pageUrl($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
        @endif

        {{-- Next --}}
        @if($currentPage < $lastPage)
            <a href="{{ $pageUrl($currentPage + 1) }}" class="page-btn" aria-label="Halaman berikutnya">&rsaquo;</a>
        @else
            <span class="page-btn page-btn-disabled" aria-disabled="true">&rsaquo;</span>
        @endif
    </nav>
    @endif
</div>
@endif

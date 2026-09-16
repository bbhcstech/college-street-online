@extends('layouts.dashboard')
@php 
        $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Catalogue';
    $logoutRoute = route('publisher.logout'); 
@endphp
@section('title', 'My Books')
@section('nav')@include('publisher.partials.nav', ['active' => 'books'])@endsection
@section('content')
    <style>
        .publisher-table-card {
            padding: 0;
            overflow: hidden;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(22, 58, 92, .05);
        }
        .publisher-book-toolbar {
            display: grid;
            grid-template-columns: minmax(250px, 1.7fr) repeat(4, minmax(120px, .8fr)) auto auto;
            align-items: center;
            gap: 9px;
            padding: 14px 16px;
            margin: 0;
            background: color-mix(in srgb, var(--a-surface-alt) 38%, var(--a-surface));
            border-bottom: 1px solid var(--a-border);
        }
        .publisher-search {
            flex: 1 1 200px;
            min-width: 170px;
        }
        .publisher-book-toolbar .a-select {
            width: 100%;
            min-width: 0;
            padding: 5px 8px;
            font-size: 0.78rem;
            height: 32px;
        }
        .publisher-export-bar {
            padding: 8px 16px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .publisher-table-scroll {
            overflow-x: auto;
            padding: 0 16px 8px;
        }
        .publisher-book-table {
            width: 100%;
            min-width: 0 !important;
            border-collapse: collapse;
            table-layout: auto;
        }
        .publisher-book-table th,
        .publisher-book-table td {
            padding: 7px 10px !important;
            font-size: 0.82rem;
            vertical-align: middle;
        }
        .publisher-book-table th {
            font-size: 0.69rem;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        .publisher-book-table th:first-child,
        .publisher-book-table td:first-child { width: 38px; }
        .publisher-book-table th:nth-child(2) { width: 22%; }
        .publisher-book-table th:nth-child(3) { width: 17%; }
        .publisher-book-table th:nth-child(4) { width: 13%; }
        .publisher-book-table th:nth-child(5),
        .publisher-book-table th:nth-child(6),
        .publisher-book-table th:nth-child(7) { width: 9%; }
        .publisher-book-table th:last-child { width: 170px; }
        .publisher-book-table .a-book-title {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 130px;
        }
        .publisher-book-table .a-book-cover-thumb,
        .publisher-book-table .a-book-cover-placeholder {
            width: 32px !important;
            height: 42px !important;
            border-radius: 4px;
            object-fit: cover;
            flex: 0 0 32px;
        }
        .publisher-book-table .a-book-title strong {
            font-size: 0.84rem;
            line-height: 1.2;
            display: block;
        }
        .publisher-book-table .a-book-title small {
            font-size: 0.7rem;
            margin-top: 1px;
            display: block;
            color: var(--a-text-muted);
        }
        .publisher-book-table .book-status-select {
            padding: 3px 6px;
            font-size: 0.72rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            height: 26px;
        }
        .publisher-book-table .book-row-actions {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .publisher-book-table .book-row-actions .btn {
            padding: 3px 8px;
            font-size: 0.72rem;
            line-height: 1.3;
            height: 26px;
            display: inline-flex;
            align-items: center;
        }
        .publisher-book-table .publisher-stock {
            padding: 2px 8px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .publisher-books-head {
            padding: 16px 18px;
            margin-bottom: 14px;
            border: 1px solid var(--a-border);
            border-radius: 14px;
            background: var(--a-surface-alt);
            box-shadow: 0 5px 16px rgba(22, 58, 92, .04);
        }
        .publisher-books-head h2 { margin: 4px 0 2px; font-size: 1.5rem; color: var(--a-text); }
        .publisher-books-head p { color: var(--a-text-muted); }
        .publisher-books-head .btn { min-height: 38px; box-shadow: 0 5px 12px rgba(22, 58, 92, .14); }
        .publisher-book-summary { gap: 11px; margin-bottom: 14px; }
        .publisher-book-summary > div { padding: 13px 16px; border-radius: 11px; background: var(--a-surface); border: 1px solid var(--a-border); box-shadow: 0 3px 12px rgba(22, 58, 92, .035); }
        .publisher-book-summary > div span { color: var(--a-text-muted); }
        .publisher-book-summary > div strong { color: var(--a-text); }
        .publisher-book-summary > div:nth-child(1) { border-left: 3px solid #3b82f6; }
        .publisher-book-summary > div:nth-child(2) { border-left: 3px solid #10b981; }
        .publisher-book-summary > div:nth-child(3) { border-left: 3px solid #f59e0b; }
        .publisher-table-scroll {
            padding: 0 14px 6px;
        }
        .publisher-book-table thead th {
            padding-top: 9px !important;
            padding-bottom: 9px !important;
            background: var(--a-surface-alt);
            color: var(--a-text-muted);
            border-top: 1px solid var(--a-border);
        }
        .publisher-book-table thead th:first-child { border-radius: 8px 0 0 8px; }
        .publisher-book-table thead th:last-child { border-radius: 0 8px 8px 0; }
        .publisher-book-table tbody td {
            height: 48px;
            border-bottom: 1px solid var(--a-border);
            color: var(--a-text);
        }
        .publisher-book-table tbody tr:nth-child(even) {
            background: color-mix(in srgb, var(--a-surface-alt) 25%, var(--a-surface));
        }
        .publisher-book-table tbody tr:hover {
            background: color-mix(in srgb, var(--a-primary) 10%, var(--a-surface));
            box-shadow: inset 3px 0 var(--a-primary);
        }
        .publisher-book-table th:first-child,
        .publisher-book-table td:first-child { text-align: center; padding-inline: 5px !important; }
        .publisher-book-table td:nth-child(3) {
            color: var(--a-text-muted);
            font-variant-numeric: tabular-nums;
        }
        .publisher-book-table td:nth-child(5) strong { color: var(--a-text); }
        .publisher-book-table .a-book-cover-thumb,
        .publisher-book-table .a-book-cover-placeholder {
            border: 1px solid var(--a-border);
            box-shadow: 0 2px 6px rgba(22, 58, 92, .12);
        }
        .publisher-book-table .book-status-select {
            border: 0;
            background-color: #e3f4ed;
            color: #087c55;
            box-shadow: inset 0 0 0 1px rgba(8, 124, 85, .06);
        }
        .publisher-book-table .book-status-select:has(option[value="inactive"]:checked) {
            background-color: #eef1f5;
            color: #5c6878;
        }
        .publisher-book-table .book-row-actions {
            padding: 3px;
            border: 1px solid var(--a-border);
            border-radius: 8px;
            background: var(--a-surface-alt);
        }
        .publisher-book-table .book-row-actions .btn {
            border-radius: 6px;
        }

        /* Dark Mode explicit overrides */
        html.dark .publisher-books-head {
            background: var(--a-surface-alt, #12314e) !important;
            border-color: var(--a-border, #1d3e5c) !important;
        }
        html.dark .publisher-book-table thead th {
            background: var(--a-surface-alt, #12314e) !important;
            color: var(--a-text-muted, #93a3be) !important;
            border-color: var(--a-border, #1d3e5c) !important;
        }
        html.dark .publisher-book-table tbody td {
            border-color: var(--a-border, #1d3e5c) !important;
            color: var(--a-text, #edf1fa) !important;
        }
        html.dark .publisher-book-table tbody td:nth-child(3) {
            color: var(--a-text-muted, #93a3be) !important;
        }
        html.dark .publisher-book-table tbody td:nth-child(5) strong {
            color: var(--a-text, #edf1fa) !important;
        }
        html.dark .publisher-book-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.02) !important;
        }
        html.dark .publisher-book-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05) !important;
        }
        html.dark .publisher-book-table .book-row-actions {
            background: var(--a-surface, #0f2a44) !important;
            border-color: var(--a-border, #1d3e5c) !important;
        }
        html.dark .publisher-book-table .book-status-select {
            background-color: rgba(16, 185, 129, 0.18) !important;
            color: #34d399 !important;
        }
        html.dark .publisher-book-table .book-status-select:has(option[value="inactive"]:checked) {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: #94a3b8 !important;
        }
        html.dark .publisher-stock {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            border: 1px solid rgba(52, 211, 153, 0.3) !important;
        }
        html.dark .publisher-stock.low {
            background-color: rgba(245, 158, 11, 0.2) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(251, 191, 36, 0.3) !important;
        }
        .publisher-book-table .book-row-actions .btn {
            border-radius: 6px;
        }
        @media (max-width: 1150px) {
            .publisher-book-toolbar { grid-template-columns: repeat(3, 1fr); }
            .publisher-book-toolbar .publisher-search { grid-column: 1 / -1; }
        }
        @media (max-width: 700px) {
            .publisher-books-head { align-items: flex-start; flex-direction: column; }
            .publisher-books-head .btn { width: 100%; justify-content: center; }
            .publisher-book-toolbar { grid-template-columns: 1fr; }
            .publisher-book-toolbar .publisher-search { grid-column: auto; }
            .publisher-book-summary { grid-template-columns: 1fr; }
        }
    </style>
    <div class="publisher-page-head publisher-books-head">
        <div><span class="analytics-eyebrow">Catalogue</span>
            <h2>My book catalogue</h2>
            <p>Search, export, and manage every title you sell.</p>
        </div>
        <a href="{{ route('publisher.books.create') }}" class="btn btn-primary">+ Add book</a>
    </div>
    <div class="publisher-book-summary">
        <div><span>Total titles</span><strong>{{ $books->total() }}</strong></div>
        <div><span>Active titles</span><strong>{{ $activeCount }}</strong></div>
        <div><span>Low stock</span><strong>{{ $lowStockCount }}</strong></div>
    </div>

    <div class="a-card publisher-table-card" data-publisher-books
        data-export-base="{{ route('publisher.books.export', 'csv') }}">
        <form method="GET" class="publisher-book-toolbar">
            <div class="publisher-search">
                <span>⌕</span>
                <input name="q" value="{{ request('q') }}" placeholder="Search title, ISBN or author">
            </div>
            <select name="category_id" class="a-select">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="a-select">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select><select name="stock" class="a-select">
                <option value="">All stock levels</option>
                <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
            </select><select name="per_page" class="a-select">
                <option value="10" @selected($books->perPage() === 10)>10 entries</option>
                <option value="25" @selected($books->perPage() === 25)>25 entries</option>
                <option value="50" @selected($books->perPage() === 50)>50 entries</option>
                <option value="100" @selected($books->perPage() === 100)>100 entries</option>
            </select>
            <button class="btn btn-primary btn-sm">Apply</button>
            @if(request()->query())
                <a href="{{ route('publisher.books.index') }}" class="btn btn-outline btn-sm">Reset</a>
            @endif
        </form>
        <div class="publisher-export-bar">
            <div>
                <strong data-selection-count>0 selected</strong>
                <span>Exports use selected rows, or all filtered books when none are selected.</span>
            </div>
            <div class="publisher-export-buttons">
                <button type="button" class="btn btn-outline btn-sm" data-copy>Copy</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="excel">Excel</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="pdf">PDF</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="print">Print</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="csv">CSV</button>
            </div>
        </div>
        <div class="publisher-table-scroll">
            <table class="a-table publisher-book-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-select-all aria-label="Select all books"></th>
                        <th>Book</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr data-export-row data-id="{{ $book->id }}">
                            <td>
                                <input type="checkbox" data-row-select aria-label="Select {{ $book->title }}">
                            </td>
                            <td>
                                <div class="a-book-title">
                                    @if($book->cover_url)
                                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="a-book-cover-thumb">
                                    @else
                                        <div class="a-book-cover-thumb a-book-cover-placeholder">📖</div>
                                    @endif
                                        <div>
                                            <strong data-cell>{{ $book->title }}</strong>
                                            <small data-cell>{{ $book->author?->name ?? 'Unknown author' }}</small>
                                        </div>
                                </div>
                            </td>
                            <td data-cell>{{ $book->isbn }}</td>
                            <td data-cell>{{ $book->category?->name ?? 'General' }}</td>
                            <td data-cell><strong>₹{{ number_format($book->price, 2) }}</strong></td>
                            <td data-cell>
                                <span class="publisher-stock {{ ($book->inventory?->quantity ?? 0) <= ($book->inventory?->low_stock_threshold ?? 5) ? 'low' : '' }}">
                                    {{ $book->inventory?->quantity ?? 0 }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('publisher.books.status', $book) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="book-status-select status-{{ $book->status }}"
                                        data-current="{{ $book->status }}" onchange="
                                        if (confirm('Change this book status?')) {
                                            this.form.submit();
                                        } else {
                                            this.value = this.dataset.current;
                                        }">
                                        <option value="active" @selected($book->status === 'active')>
                                            Active
                                        </option>
                                        <option value="inactive" @selected($book->status === 'inactive')>
                                            Inactive
                                        </option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="book-row-actions">
                                    <a href="{{ route('books.show', $book) }}" target="_blank" class="btn btn-outline btn-sm">
                                         View
                                    </a>
                                    <a href="{{ route('publisher.books.edit', $book) }}" class="btn btn-outline btn-sm">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('publisher.books.destroy', $book) }}"
                                        onsubmit="return confirm('Archive this book from the catalogue?')">@csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger-outline btn-sm">Archive</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="analytics-empty">No books match your filters.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="publisher-table-footer">
            <span>
                Showing {{ $books->firstItem() ?? 0 }}–{{ $books->lastItem() ?? 0 }} of
                {{ $books->total() }} books
            </span>
            @if($books->hasPages())
                    <nav class="order-pagination">
                        @if($books->onFirstPage())
                            <span class="disabled">Previous</span>
                        @else
                            <a href="{{ $books->previousPageUrl() }}">Previous</a>
                        @endif
                        @foreach(range(1, $books->lastPage()) as $page)
                        <a href="{{ $books->url($page) }}" class="{{ $books->currentPage() === $page ? 'active' : '' }}">
                            {{ $page }}
                        </a>
                        @endforeach
                        @if($books->hasMorePages())
                            <a href="{{ $books->nextPageUrl() }}">Next</a>
                        @else
                            <span class="disabled">Next</span>
                        @endif
                    </nav>
            @endif
        </div>
    </div>

    <script>
    (() => {
        const root = document.querySelector('[data-publisher-books]');
        const rows = [...root.querySelectorAll('[data-export-row]')];
        const all = root.querySelector('[data-select-all]');
        const count = root.querySelector('[data-selection-count]');

        const selected = () =>
            rows.filter(
                (row) => row.querySelector('[data-row-select]').checked
            );

        const update = () => {
            const n = selected().length;

            count.textContent = `${n} selected`;

            if (all) {
                all.checked = n === rows.length && n > 0;
                all.indeterminate = n > 0 && n < rows.length;
            }
        };

        all?.addEventListener('change', () => {
            rows.forEach((row) => {
                row.querySelector('[data-row-select]').checked = all.checked;
            });

            update();
        });

        rows.forEach((row) => {
            row.querySelector('[data-row-select]')
                .addEventListener('change', update);
        });

        const ids = () =>
            selected()
                .map((row) => row.dataset.id)
                .join(',');

        root.querySelector('[data-copy]')?.addEventListener(
            'click',
            async (e) => {
                const chosen = selected().length ? selected() : rows;

                const text = chosen
                    .map((row) =>
                        [...row.querySelectorAll('[data-cell]')]
                            .map((cell) => cell.textContent.trim())
                            .join('\t')
                    )
                    .join('\n');

                await navigator.clipboard.writeText(text);

                e.target.textContent = 'Copied';

                setTimeout(() => {
                    e.target.textContent = 'Copy';
                }, 1200);
            }
        );

        root.querySelectorAll('[data-export]').forEach((button) => {
            button.addEventListener('click', () => {
                const url = new URL(
                    root.dataset.exportBase.replace(
                        /csv$/,
                        button.dataset.export
                    ),
                    location.origin
                );

                const params = new URLSearchParams(location.search);

                params.delete('page');

                if (ids()) {
                    params.set('ids', ids());
                }

                url.search = params;

                if (
                    button.dataset.export === 'print' ||
                    button.dataset.export === 'pdf'
                ) {
                    window.open(url, '_blank');
                } else {
                    location.href = url;
                }
            });
        });
    })();
</script>
@endsection

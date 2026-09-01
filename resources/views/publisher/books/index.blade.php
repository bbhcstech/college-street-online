@extends('layouts.dashboard')
@php 
        $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Catalogue';
    $logoutRoute = route('publisher.logout'); 
@endphp
@section('title', 'My Books')
@section('nav')
    <div class="nav-group">
        <div class="nav-group-title">Overview</div>
        <a href="{{ route('publisher.dashboard') }}" class="nav-link">
            <span class="nav-icon">▣</span>
            <span>Dashboard</span>
        </a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Catalogue</div>
        <a href="{{ route('publisher.books.index') }}" class="nav-link active">
            <span class="nav-icon">📖</span>
            <span>My Books</span>
        </a>
        <a href="{{ route('publisher.inventory.index') }}" class="nav-link">
            <span class="nav-icon">📦</span>
            <span>Inventory</span>
        </a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Marketing</div>
        <a href="{{ route('publisher.coupons.index') }}" class="nav-link">
            <span class="nav-icon">🏷</span>
            <span>Coupons & Offers</span>
        </a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Sales</div>
        <a href="{{ route('publisher.orders.index') }}" class="nav-link">
            <span class="nav-icon">🚚</span>
            <span>Orders</span>
        </a>
    </div>
@endsection
@section('content')
    <div class="publisher-page-head">
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
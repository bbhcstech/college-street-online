@extends('layouts.dashboard')
@php $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Operations';
$logoutRoute = route('admin.logout'); @endphp
@section('title', 'Bulk Order Requests')
@section('nav')@include('admin.partials.nav', ['active' => 'bulk-orders'])@endsection
@section('content')
    <div class="publisher-page-head">
        <div><span class="analytics-eyebrow">Institutional sales</span>
            <h2>Bulk order requests</h2>
            <p>Review, quote, export, and track high-volume purchase enquiries.</p>
        </div>
    </div>
    <div class="order-summary">
        <div><span>Total requests</span><strong>{{ $totalRequests }}</strong></div>
        <div><span>New requests</span><strong>{{ $newRequests }}</strong></div>
        <div><span>Quoted pipeline</span><strong>₹{{ number_format($quotedValue, 2) }}</strong></div>
    </div>

    <div class="a-card publisher-table-card" data-bulk-table
        data-export-base="{{ route('admin.bulk-orders.export', 'csv') }}">
        <form method="GET" class="bulk-table-toolbar">
            <div class="publisher-search"><span>⌕</span><input name="q" value="{{ request('q') }}"
                    placeholder="Search reference, institution or contact"></div><select name="status" class="a-select">
                <option value="">All statuses</option>@foreach(\App\Models\BulkOrderRequest::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select><input type="date" name="date_from" value="{{ request('date_from') }}" class="a-input"
                title="From date"><input type="date" name="date_to" value="{{ request('date_to') }}" class="a-input"
                title="To date"><select name="per_page" class="a-select">
                <option value="10" @selected($requests->perPage() === 10)>10 entries</option>
                <option value="25" @selected($requests->perPage() === 25)>25 entries</option>
                <option value="50" @selected($requests->perPage() === 50)>50 entries</option>
                <option value="100" @selected($requests->perPage() === 100)>100 entries</option>
            </select><button class="btn btn-primary btn-sm">Apply</button>@if(request()->query())<a
            href="{{ route('admin.bulk-orders.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
        </form>
        <div class="publisher-export-bar">
            <div><strong data-selection-count>0 selected</strong><span>Exports use selected rows, or all filtered requests
                    when none are selected.</span></div>
            <div class="publisher-export-buttons"><button type="button" class="btn btn-outline btn-sm"
                    data-copy>Copy</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="excel">Excel</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="pdf">PDF</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="print">Print</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="csv">CSV</button></div>
        </div>
        <div class="publisher-table-scroll">
            <table class="a-table bulk-data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-select-all aria-label="Select all requests"></th>
                        <th>Reference</th>
                        <th>Institution</th>
                        <th>Contact</th>
                        <th>Submitted</th>
                        <th>Quote</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $item)
                        <tr data-export-row data-id="{{ $item->id }}">
                            <td><input type="checkbox" data-row-select aria-label="Select BOR{{ $item->id }}"></td>
                            <td><strong data-cell>#BOR{{ $item->id }}</strong></td>
                            <td><strong
                                    data-cell>{{ $item->institution_name }}</strong><small>{{ Str::limit($item->requirements, 55) }}</small>
                            </td>
                            <td><strong data-cell>{{ $item->contact_name }}</strong><small
                                    data-cell>{{ $item->email }}</small><small>{{ $item->phone }}</small></td>
                            <td data-cell>
                                {{ $item->created_at->format('d M Y') }}<small>{{ $item->created_at->format('h:i A') }}</small>
                            </td>
                            <td data-cell>
                                <strong>{{ $item->quoted_amount ? '₹' . number_format($item->quoted_amount, 2) : '—' }}</strong>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.bulk-orders.status', $item) }}">@csrf
                                    @method('PATCH')<select name="status" class="bulk-status-select status-{{ $item->status }}"
                                        data-current="{{ $item->status }}"
                                        onchange="if(this.value==='quoted'&&!{{ $item->quoted_amount ? 'true' : 'false' }}){alert('Open the request and enter a quote amount first.');this.value=this.dataset.current}else if(confirm('Change this request status?'))this.form.submit();else this.value=this.dataset.current">@foreach(\App\Models\BulkOrderRequest::STATUSES as $status)
                                            <option value="{{ $status }}" @selected($item->status === $status)>{{ ucfirst($status) }}
                                        </option>@endforeach
                                    </select></form>
                            </td>
                            <td><a href="{{ route('admin.bulk-orders.show', $item) }}" class="btn btn-outline btn-sm">View &
                                    manage</a></td>
                        </tr>
                    @empty<tr>
                        <td colspan="8">
                            <div class="analytics-empty">No bulk requests match your filters.</div>
                        </td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="publisher-table-footer"><span>Showing {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} of
                {{ $requests->total() }} requests</span>@if($requests->hasPages())
                    <nav class="order-pagination">@if($requests->onFirstPage())<span class="disabled">Previous</span>@else<a
                    href="{{ $requests->previousPageUrl() }}">Previous</a>@endif
                        @foreach(range(1, $requests->lastPage()) as $page)<a href="{{ $requests->url($page) }}"
                        class="{{ $requests->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                        @if($requests->hasMorePages())<a href="{{ $requests->nextPageUrl() }}">Next</a>@else<span
                class="disabled">Next</span>@endif</nav>@endif
        </div>
    </div>
    <script>(() => { const root = document.querySelector('[data-bulk-table]'), rows = [...root.querySelectorAll('[data-export-row]')], all = root.querySelector('[data-select-all]'), count = root.querySelector('[data-selection-count]'); const selected = () => rows.filter(r => r.querySelector('[data-row-select]').checked), update = () => { const n = selected().length; count.textContent = `${n} selected`; if (all) { all.checked = n === rows.length && n > 0; all.indeterminate = n > 0 && n < rows.length } }; all?.addEventListener('change', () => { rows.forEach(r => r.querySelector('[data-row-select]').checked = all.checked); update() }); rows.forEach(r => r.querySelector('[data-row-select]').addEventListener('change', update)); const ids = () => selected().map(r => r.dataset.id).join(','); root.querySelector('[data-copy]')?.addEventListener('click', async e => { const chosen = selected().length ? selected() : rows, text = chosen.map(r => [...r.querySelectorAll('[data-cell]')].map(c => c.textContent.trim()).join('\t')).join('\n'); await navigator.clipboard.writeText(text); e.target.textContent = 'Copied'; setTimeout(() => e.target.textContent = 'Copy', 1200) }); root.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => { const url = new URL(root.dataset.exportBase.replace(/csv$/, b.dataset.export), location.origin), params = new URLSearchParams(location.search); params.delete('page'); if (ids()) params.set('ids', ids()); url.search = params; b.dataset.export === 'print' || b.dataset.export === 'pdf' ? window.open(url, '_blank') : location.href = url })); })();</script>
@endsection
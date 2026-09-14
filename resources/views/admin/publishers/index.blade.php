@extends('layouts.dashboard')
@php 
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
    $logoutRoute = route('admin.logout'); 

    $totalCount = \App\Models\Publisher::count();
    $approvedCount = \App\Models\Publisher::where('approval_status', 'approved')->count();
    $pendingCount = \App\Models\Publisher::where('approval_status', 'pending')->count();
    $rejectedCount = \App\Models\Publisher::where('approval_status', 'rejected')->count();
@endphp

@section('title', 'Publishers')
@section('nav')@include('admin.partials.nav', ['active' => 'publishers'])@endsection

@section('content')
<!-- Page Header -->
<div class="publisher-page-head publisher-management-head" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <span class="analytics-eyebrow" style="color: #6366f1; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Marketplace Partners</span>
        <h2 style="font-size: 1.35rem; font-weight: 700; color: #111827; margin: 2px 0;">Publisher Management</h2>
        <p style="color: #6b7280; font-size: 0.82rem; margin: 0;">Search, review, approve, export, and manage publisher accounts.</p>
    </div>
    <a href="{{ route('admin.publishers.create') }}" class="btn btn-primary" style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; border-radius: 6px;">
        + Add Publisher
    </a>
</div>

@if(session('success'))
    <div style="padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; margin-bottom: 16px; font-weight: 500; display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
        <span style="font-size: 1rem;">✓</span> {{ session('success') }}
    </div>
@endif

<!-- Summary KPI Cards -->
<div class="publisher-kpi-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px;">
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #3b82f6; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #6b7280; letter-spacing: 0.5px;">Total Publishers</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #1e293b; margin-top: 2px;">{{ number_format($totalCount) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #10b981; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #047857; letter-spacing: 0.5px;">Approved Partners</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #065f46; margin-top: 2px;">{{ number_format($approvedCount) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #f59e0b; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #b45309; letter-spacing: 0.5px;">Pending Approval</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #92400e; margin-top: 2px;">{{ number_format($pendingCount) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #ef4444; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #b91c1c; letter-spacing: 0.5px;">Rejected</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #991b1b; margin-top: 2px;">{{ number_format($rejectedCount) }}</strong>
    </div>
</div>

<!-- Main Table Card Container -->
<div class="a-card publisher-management-card" style="border-radius: 8px; overflow: hidden; background: #ffffff; border: 1px solid var(--a-border, #e5e7eb); box-shadow: 0 1px 3px rgba(0,0,0,0.04);" data-publisher-table data-export-base="{{ route('admin.publishers.export', 'csv') }}">
    
    <!-- SINGLE ROW COMPACT FILTER BAR -->
    <form method="GET" class="publisher-management-filters" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; width: 100%; flex-wrap: nowrap; overflow-x: auto; background: #ffffff; border-bottom: 1px solid #e5e7eb;">
        <!-- Search Input -->
        <div style="flex: 0 1 240px; min-width: 180px; display: flex; align-items: center; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 8px; height: 34px;">
            <span style="color: #9ca3af; margin-right: 6px; font-size: 0.85rem;">🔍</span>
            <input name="q" value="{{ request('q') }}" placeholder="Search business, contact, email..." 
                   style="border: none; background: transparent; width: 100%; height: 100%; outline: none; font-size: 0.82rem; color: #1f2937;">
        </div>

        <!-- Approval Status Dropdown -->
        <select name="status" style="width: 130px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="">All Statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>

        <!-- Per Page Dropdown -->
        <select name="per_page" style="width: 110px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="10" @selected($publishers->perPage() === 10)>10 entries</option>
            <option value="25" @selected($publishers->perPage() === 25)>25 entries</option>
            <option value="50" @selected($publishers->perPage() === 50)>50 entries</option>
            <option value="100" @selected($publishers->perPage() === 100)>100 entries</option>
        </select>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="submit" class="btn btn-primary" style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                Filter
            </button>
            @if(request()->hasAny(['q', 'status', 'per_page']))
                <a href="{{ route('admin.publishers.index') }}" class="btn btn-outline" style="height: 34px; padding: 0 10px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- EXPORT TOOLBAR -->
    <div class="publisher-export-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
        <div>
            <strong data-selection-count style="font-size: 0.82rem; color: #374151;">0 selected</strong>
            <span style="font-size: 0.78rem; color: #6b7280; margin-left: 6px;">
                Export filtered publishers or selected rows.
            </span>
        </div>
        <div class="publisher-export-buttons" style="display: flex; gap: 4px;">
            <button type="button" class="btn btn-outline btn-sm" data-copy style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Copy</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="excel" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Excel</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="pdf" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">PDF</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="print" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Print</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="csv" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">CSV</button>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="a-table publisher-management-table" style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.84rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="width: 36px; text-align: center; padding: 10px 6px;"><input type="checkbox" data-select-all aria-label="Select all publishers"></th>
                    <th style="padding: 10px 12px;">Publisher / Business</th>
                    <th style="padding: 10px 12px;">Contact Details</th>
                    <th style="padding: 10px 12px;">Books</th>
                    <th style="padding: 10px 12px;">Joined Date</th>
                    <th style="padding: 10px 12px;">Status</th>
                    <th style="padding: 10px 12px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($publishers as $publisher)
                    <tr data-export-row data-id="{{ $publisher->id }}" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="text-align: center; padding: 10px 6px;">
                            <input type="checkbox" data-row-select aria-label="Select {{ $publisher->business_name }}">
                        </td>
                        <td style="padding: 10px 12px;">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #3b82f6; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                    {{ strtoupper(substr($publisher->business_name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong data-cell style="display: block; font-size: 0.88rem; color: #0f172a; line-height: 1.25;">{{ $publisher->business_name }}</strong>
                                    <span style="font-size: 0.75rem; color: #64748b;">{{ $publisher->contact_details ?: 'No address details' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 10px 12px;">
                            <strong data-cell style="display: block; font-size: 0.84rem; color: #1e293b;">{{ $publisher->user->name ?? '—' }}</strong>
                            <span data-cell style="font-size: 0.75rem; color: #64748b;">{{ $publisher->user->email ?? '—' }}</span>
                        </td>
                        <td data-cell style="padding: 10px 12px;">
                            <span style="display: inline-block; padding: 2px 7px; border-radius: 10px; background: #e2e8f0; color: #334155; font-weight: 600; font-size: 0.75rem;">
                                {{ number_format($publisher->books_count) }} titles
                            </span>
                        </td>
                        <td data-cell style="padding: 10px 12px; font-size: 0.82rem; color: #475569;">
                            {{ $publisher->created_at->format('d M Y') }}
                        </td>
                        <td style="padding: 10px 12px;">
                            <form method="POST" action="{{ route('admin.publishers.approval', $publisher) }}" data-status-form>
                                @csrf 
                                @method('PATCH')
                                <select name="approval_status" 
                                        style="padding: 3px 6px; border-radius: 5px; font-size: 0.75rem; font-weight: 600; border: 1px solid #cbd5e1; outline: none; cursor: pointer; background: {{ $publisher->approval_status === 'approved' ? '#ecfdf5' : ($publisher->approval_status === 'pending' ? '#fffbeb' : '#fef2f2') }}; color: {{ $publisher->approval_status === 'approved' ? '#065f46' : ($publisher->approval_status === 'pending' ? '#92400e' : '#991b1b') }};"
                                        onchange="if(confirm('Change approval status for {{ addslashes($publisher->business_name) }}?')) this.form.submit(); else this.value=this.dataset.current" 
                                        data-current="{{ $publisher->approval_status }}">
                                    <option value="pending" @selected($publisher->approval_status === 'pending')>⏳ Pending</option>
                                    <option value="approved" @selected($publisher->approval_status === 'approved')>✓ Approved</option>
                                    <option value="rejected" @selected($publisher->approval_status === 'rejected')>✕ Rejected</option>
                                </select>
                            </form>
                        </td>
                        <td style="padding: 10px 12px; text-align: right;">
                            <div style="display: flex; gap: 5px; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('admin.publishers.edit', $publisher) }}" class="btn btn-outline btn-sm" style="height: 28px; padding: 0 8px; font-size: 0.75rem; border-radius: 5px;" title="Edit publisher profile">
                                    ✏ Edit
                                </a>
                                <form method="POST" action="{{ route('admin.publishers.destroy', $publisher) }}" onsubmit="return confirm('Remove publisher \'{{ addslashes($publisher->business_name) }}\'?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" style="height: 28px; padding: 0 8px; font-size: 0.75rem; border-radius: 5px;" title="Remove publisher">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                            No publishers match your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION FOOTER -->
    <div style="padding: 10px 14px; border-top: 1px solid #e2e8f0; background: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span style="font-size: 0.78rem; color: #64748b;">
            Showing {{ $publishers->firstItem() ?? 0 }}–{{ $publishers->lastItem() ?? 0 }} of {{ $publishers->total() }} publishers
        </span>
        @if($publishers->hasPages())
            <div>
                {{ $publishers->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    (() => { 
        const root = document.querySelector('[data-publisher-table]'), 
              rows = [...root.querySelectorAll('[data-export-row]')], 
              all = root.querySelector('[data-select-all]'), 
              count = root.querySelector('[data-selection-count]'); 
        
        const selected = () => rows.filter(r => r.querySelector('[data-row-select]').checked); 
        const update = () => { 
            const n = selected().length; 
            count.textContent = `${n} selected`; 
            if (all) {
                all.checked = n === rows.length && n > 0; 
                all.indeterminate = n > 0 && n < rows.length; 
            }
        }; 
        
        all?.addEventListener('change', () => { 
            rows.forEach(r => r.querySelector('[data-row-select]').checked = all.checked); 
            update(); 
        }); 
        
        rows.forEach(r => r.querySelector('[data-row-select]').addEventListener('change', update)); 
        
        const ids = () => selected().map(r => r.dataset.id).join(','); 
        
        root.querySelector('[data-copy]').addEventListener('click', async e => { 
            const chosen = selected().length ? selected() : rows, 
                  text = chosen.map(r => [...r.querySelectorAll('[data-cell]')].map(c => c.textContent.trim()).join('\t')).join('\n'); 
            await navigator.clipboard.writeText(text); 
            e.target.textContent = 'Copied'; 
            setTimeout(() => e.target.textContent = 'Copy', 1200); 
        }); 
        
        root.querySelectorAll('[data-export]').forEach(button => button.addEventListener('click', () => { 
            const base = root.dataset.exportBase.replace(/csv$/, button.dataset.export), 
                  url = new URL(base, location.origin), 
                  params = new URLSearchParams(location.search); 
            params.delete('page'); 
            if (ids()) params.set('ids', ids()); 
            url.search = params; 
            button.dataset.export === 'print' || button.dataset.export === 'pdf' ? window.open(url, '_blank') : location.href = url; 
        })); 
    })();
</script>
@endsection

@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $logoutRoute = route('admin.logout');
    $crumb = 'Access Control';
    $active = 'administrators';
@endphp
@section('title', 'Administrators')
@section('nav') @include('admin.partials.nav', ['active' => $active]) @endsection

@section('content')
<div class="page-heading-row" style="margin-bottom: 24px;">
    <div>
        <span class="a-eyebrow" style="text-transform: uppercase; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.8px; color: #64748b;">Access Control &amp; Hierarchy</span>
        <h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 4px 0;">Administrators</h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Manage authorized admin accounts, roles, and security permissions.</p>
    </div>
    <a href="{{ route('admin.administrators.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; padding: 10px 18px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <span style="font-size: 1.1rem; line-height: 1;">+</span> Add Administrator
    </a>
</div>

<!-- KPI Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="a-card" style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #3b82f6; margin-bottom: 0;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Total Admins</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $stats['total'] ?? 0 }}</div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">🛡️</div>
    </div>
    <div class="a-card" style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #f59e0b; margin-bottom: 0;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Super Admins</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #b45309; margin-top: 4px;">{{ $stats['super_admins'] ?? 0 }}</div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">👑</div>
    </div>
    <div class="a-card" style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #6366f1; margin-bottom: 0;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Standard Admins</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $stats['standard_admins'] ?? 0 }}</div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">👤</div>
    </div>
    <div class="a-card" style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #10b981; margin-bottom: 0;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Active Accounts</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #047857; margin-top: 4px;">{{ $stats['active'] ?? 0 }}</div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">✅</div>
    </div>
</div>

@if(session('success'))
    <div style="padding: 12px 18px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.1rem;">✅</span> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding: 12px 18px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.1rem;">⚠️</span> {{ session('error') }}
    </div>
@endif

<!-- Main Card & Data Table -->
<div class="a-card" style="padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);">
    <!-- Filter Bar -->
    <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
        <div style="position: relative; flex: 1; min-width: 260px;">
            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.95rem;">🔍</span>
            <input type="search" name="q" value="{{ request('q') }}" class="a-input" placeholder="Search administrator by name or email..." style="padding-left: 38px; border-radius: 8px; border: 1px solid #cbd5e1; height: 42px;">
        </div>
        <button class="btn btn-primary" type="submit" style="padding: 0 20px; height: 42px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">Search</button>
        @if(request()->filled('q'))
            <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline" style="padding: 0 16px; height: 42px; border-radius: 8px; display: inline-flex; align-items: center;">Reset Filter</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="a-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; text-align: left;">Administrator</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; text-align: left;">Email Address</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; text-align: left;">Status</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; text-align: left;">Joined Date</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($administrators as $administrator)
                @php
                    $initials = strtoupper(substr($administrator->name, 0, 2));
                    $bgGradient = $administrator->isSuperAdmin() ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)';
                @endphp
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $bgGradient }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                {{ $initials }}
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">{{ $administrator->name }}</span>
                                    @if($administrator->is(auth()->user()))
                                        <span style="background: rgba(59, 130, 246, 0.12); color: #2563eb; font-size: 0.68rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.2);">You</span>
                                    @endif
                                </div>
                                <div style="margin-top: 3px;">
                                    @if($administrator->isSuperAdmin())
                                        <span style="background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.72rem; padding: 2px 8px; border-radius: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">👑 Super Admin</span>
                                    @else
                                        <span style="background: rgba(99, 102, 241, 0.1); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.2); font-size: 0.72rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">🛡️ Standard Admin</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle; color: #475569; font-weight: 500; font-size: 0.9rem;">
                        {{ $administrator->email }}
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        @if($administrator->status === 'active')
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981;"></span> Active
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #ef4444;"></span> Suspended
                            </span>
                        @endif
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle; color: #64748b; font-size: 0.88rem; font-weight: 500;">
                        {{ $administrator->created_at->format('d M Y') }}
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle; text-align: right;">
                        @if($administrator->isSuperAdmin() && !auth()->user()->isSuperAdmin())
                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-weight: 600; border: 1px solid #cbd5e1;">🔒 Protected</span>
                        @else
                            <div style="display: inline-flex; gap: 8px; align-items: center; justify-content: flex-end;">
                                <a href="{{ route('admin.administrators.edit', $administrator) }}" class="btn btn-outline btn-sm" style="padding: 5px 12px; font-weight: 600; border-radius: 6px;">Edit</a>
                                @if(! $administrator->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.administrators.status', $administrator) }}" style="display: inline;">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $administrator->status === 'active' ? 'suspended' : 'active' }}">
                                        <button type="submit" class="btn btn-sm {{ $administrator->status === 'active' ? 'btn-danger' : 'btn-primary' }}" style="padding: 5px 12px; font-weight: 600; border-radius: 6px;" onclick="return confirm('Are you sure you want to {{ $administrator->status === 'active' ? 'deactivate' : 'activate' }} this administrator?')">
                                            {{ $administrator->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8; font-weight: 500;">
                        No administrator accounts found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">
        {{ $administrators->links() }}
    </div>
</div>
@endsection

@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Account';
    $logoutRoute = route('publisher.logout');
    $profileUpdateRoute = route('publisher.profile.update');
    $passwordUpdateRoute = route('publisher.password.update');
@endphp
@section('title', 'My Profile')
@section('nav')
    <div class="nav-group">
        <div class="nav-group-title">Overview</div>
        <a href="{{ route('publisher.dashboard') }}" class="nav-link"><span
                class="nav-icon">&#9635;</span><span>Dashboard</span></a>
        <a href="{{ route('publisher.profile.edit') }}" class="nav-link active"><span
                class="nav-icon">&#128100;</span><span>My Profile</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Catalogue</div><a href="{{ route('publisher.books.index') }}" class="nav-link"><span
                class="nav-icon">&#128214;</span><span>My Books</span></a><a href="{{ route('publisher.inventory.index') }}"
            class="nav-link"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span
                class="nav-icon">&#128666;</span><span>Orders</span></a>
    </div>
@endsection
@section('content')@include('partials.profile-forms')@endsection
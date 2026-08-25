@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard'); $brandLabel = 'Admin Console'; $crumb = 'Account'; $logoutRoute = route('admin.logout');
    $profileUpdateRoute = route('admin.profile.update'); $passwordUpdateRoute = route('admin.password.update');
@endphp
@section('title', 'My Profile')
@section('nav')@include('admin.partials.nav', ['active' => 'profile'])@endsection
@section('content')@include('partials.profile-forms')@endsection

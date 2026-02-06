@extends('user.layouts.app')
@section('title', $user->name . ' | Profile')
@section('content')
    <div class="container-lg py-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @include('user.pages.profile.sections.show.profile-header')
        @include('user.pages.profile.sections.show.stats')
        @include('user.pages.profile.sections.show.platform-profiles')
    </div>
@endsection

@extends('user.layouts.app')
@section('title', $user->name . ' | Profile')
@push('styles')
    <style>
        .sync-button .bi.spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .card.transition:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
        }
    </style>
@endpush
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
@push('scripts')
    @include('user.pages.profile.script')
@endpush

@extends('user.layouts.app')
@section('title', 'Edit Profile')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('web/css/custom-select2.css') }}">
@endpush
@section('content')
    <div class="container-lg py-5">
        <div class="row">
            @include('user.pages.profile.sections.edit.sidebar')
            <div class="col-lg-9">
                @include('user.pages.profile.sections.edit.alert')
                <div class="tab-content">
                    @include('user.pages.profile.sections.edit.profile-info')
                    @include('user.pages.profile.sections.edit.profile-platform')
                    @include('user.pages.profile.sections.edit.social-links')
                    @include('user.pages.profile.sections.edit.profile-security')
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('user.pages.profile.script')
@endpush

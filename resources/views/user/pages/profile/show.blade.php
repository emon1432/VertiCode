@extends('user.layouts.app')
@section('title', $user->name . ' | Profile')
@section('content')
    <div class="container-lg py-5">
        @include('user.pages.profile.sections.profile-header')
        @include('user.pages.profile.sections.stats')
        @include('user.pages.profile.sections.platform-profiles')
    </div>
@endsection

@extends('user.layouts.app')
@section('title', $user->name . ' | Profile')
@section('content')
    <div class="container-lg py-5">
        @include('user.pages.profile.sections.show.profile-header')
        @include('user.pages.profile.sections.show.stats')
        @include('user.pages.profile.sections.show.platform-profiles')
    </div>
@endsection

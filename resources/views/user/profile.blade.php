@extends('user.layouts.master')
@section('title', 'Dashboard')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">User Profile</h3>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Username:</strong> {{ $user->username }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

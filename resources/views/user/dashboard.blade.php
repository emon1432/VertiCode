@extends('user.layouts.app')
@section('title', 'Dashboard')
@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6>Total Platforms</h6>
                    <h3>{{ $summary['total_platforms'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6>Total Solved</h6>
                    <h3>{{ $summary['total_solved'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6>Max Rating</h6>
                    <h3>{{ $summary['max_rating'] ?? 'N/A' }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-0">Connected Platforms</h5>
                </div>
                <div class="col-md-6 text-end">
                    <form method="POST" action="{{ route('user.sync') }}">
                        @csrf
                        @php
                            $recent = $user->last_synced_at && $user->last_synced_at->gt(now()->subSeconds(60));
                        @endphp

                        <button class="btn btn-primary mb-3" {{ $recent ? 'disabled' : '' }}>
                            {{ $recent ? 'Recently Synced' : 'Sync All Platforms' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Platform</th>
                        <th>Handle</th>
                        <th>Rating</th>
                        <th>Solved</th>
                        <th>Last Sync</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($platformProfiles as $profile)
                        <tr>
                            {{-- Platform --}}
                            <td>
                                <strong>{{ $profile['platform'] }}</strong>
                            </td>

                            {{-- Handle --}}
                            <td>
                                <a href="{{ $profile['profile_url'] }}" target="_blank" class="text-decoration-none">
                                    {{ $profile['handle'] }}
                                </a>
                            </td>

                            {{-- Rating --}}
                            <td>
                                @if ($profile['rating'])
                                    {{ $profile['rating'] }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            {{-- Solved --}}
                            <td>
                                <strong>{{ $profile['total_solved'] }}</strong>

                                {{-- LeetCode breakdown --}}
                                @if ($profile['platform_key'] === 'leetcode')
                                    <div class="small text-muted mt-1">
                                        Easy: {{ $profile['extra']['easy'] ?? 0 }},
                                        Medium: {{ $profile['extra']['medium'] ?? 0 }},
                                        Hard: {{ $profile['extra']['hard'] ?? 0 }}
                                    </div>
                                @endif
                                @if ($profile['platform_key'] === 'hackerearth')
                                    <div class="small text-muted mt-1">
                                        Public problem statistics not available
                                    </div>
                                @endif
                            </td>

                            {{-- Last Sync --}}
                            <td>
                                @if ($user->last_synced_at)
                                    <span class="text-muted">
                                        Last synced {{ $user->last_synced_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        Never synced
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

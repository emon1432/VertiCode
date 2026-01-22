@extends('user.layouts.app')
@section('title', 'Dashboard')
@section('content')

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
            Connected Platforms
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
                    @forelse ($platformProfiles as $profile)
                        <tr>
                            <td>{{ $profile['platform'] }}</td>
                            <td>{{ $profile['handle'] }}</td>
                            <td>{{ $profile['rating'] ?? 'N/A' }}</td>
                            <td>{{ $profile['total_solved'] }}</td>
                            <td>{{ $profile['last_synced_at'] ?? 'Never' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No platforms connected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

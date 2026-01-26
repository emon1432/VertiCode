@extends('user.layouts.app')

@section('title', 'Platforms')

@section('content')

    <h4 class="mb-3">Manage Platforms</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Add Platform</div>
        <div class="card-body">
            <form method="POST" action="{{ route('user.platform-profiles.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <select name="platform" class="form-control" required>
                            <option value="">Select platform</option>
                            @foreach ($platforms as $platform)
                                <option value="{{ $platform->name }}">
                                    {{ $platform->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="handle" class="form-control" placeholder="Enter handle" required>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </div>

                @error('platform')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                @error('handle')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Connected Platforms</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Handle</th>
                        <th>Rating</th>
                        <th>Solved</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($profiles as $profile)
                        <tr>
                            <td>{{ $profile->platform->display_name }}</td>
                            <td>{{ $profile->handle }}</td>
                            <td>{{ $profile->rating ?? 'N/A' }}</td>
                            <td>{{ $profile->total_solved }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('user.platform-profiles.destroy', $profile) }}"
                                        onsubmit="return confirm('Remove this platform?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                No platforms added yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

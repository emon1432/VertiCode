@extends('user.layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-lg py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3 fw-600">Settings</h6>
                    <nav class="nav flex-column gap-2">
                        <a href="#profile-info" class="nav-link text-decoration-none active" data-bs-toggle="pill">
                            <i class="bi bi-person"></i> Profile Information
                        </a>
                        <a href="#profile-picture" class="nav-link text-decoration-none" data-bs-toggle="pill">
                            <i class="bi bi-image"></i> Profile Picture
                        </a>
                        <a href="#social-links" class="nav-link text-decoration-none" data-bs-toggle="pill">
                            <i class="bi bi-share"></i> Social Links
                        </a>
                        <a href="#security" class="nav-link text-decoration-none" data-bs-toggle="pill">
                            <i class="bi bi-shield-lock"></i> Security
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-circle me-2" style="margin-top: 2px;"></i>
                        <div>
                            <strong>Please correct the following errors:</strong>
                            <ul class="mb-0 mt-2 ms-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="tab-content">
                <!-- Profile Information Tab -->
                <div class="tab-pane fade show active" id="profile-info">
                    <div class="card">
                        <div class="card-header bg-white border-bottom-0 pt-4">
                            <h5 class="mb-0">
                                <i class="bi bi-person"></i> Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.profile.update', auth()->user()->username) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Full Name -->
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required class="form-control @error('name') is-invalid @enderror" placeholder="Enter your full name">
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-600">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email address">
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Username -->
                                <div class="mb-4">
                                    <label for="username" class="form-label fw-600">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">@</span>
                                        <input type="text" id="username" name="username" value="{{ old('username', auth()->user()->username) }}" class="form-control border-start-0 @error('username') is-invalid @enderror" placeholder="your_username">
                                    </div>
                                    @error('username')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Bio -->
                                <div class="mb-4">
                                    <label for="bio" class="form-label fw-600">Bio</label>
                                    <textarea id="bio" name="bio" rows="4" class="form-control @error('bio') is-invalid @enderror" placeholder="Tell us about yourself...">{{ old('bio', auth()->user()->bio) }}</textarea>
                                    <small class="text-muted d-block mt-2">Maximum 500 characters</small>
                                    @error('bio')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Location -->
                                <div class="mb-4">
                                    <label for="location" class="form-label fw-600">Location</label>
                                    <input type="text" id="location" name="location" value="{{ old('location', auth()->user()->location) }}" class="form-control @error('location') is-invalid @enderror" placeholder="City, Country">
                                    @error('location')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Website -->
                                <div class="mb-4">
                                    <label for="website" class="form-label fw-600">Website</label>
                                    <input type="url" id="website" name="website" value="{{ old('website', auth()->user()->website) }}" class="form-control @error('website') is-invalid @enderror" placeholder="https://example.com">
                                    @error('website')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-2">
                                    <a href="{{ route('user.profile.show', auth()->user()->username) }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Profile Picture Tab -->
                <div class="tab-pane fade" id="profile-picture">
                    <div class="card">
                        <div class="card-header bg-white border-bottom-0 pt-4">
                            <h5 class="mb-0">
                                <i class="bi bi-image"></i> Profile Picture
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.profile.update', auth()->user()->username) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Current Avatar -->
                                    <div class="col-md-4 mb-4">
                                        <p class="text-muted mb-3" style="font-size: 0.9rem;">Current Picture</p>
                                        <div class="rounded border d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; background: #f8f9fa;">
                                            @if(auth()->user()->profile_photo_path)
                                                <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}" class="rounded w-100 h-100" style="object-fit: cover;">
                                            @elseif(auth()->user()->profile_photo_url)
                                                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="rounded w-100 h-100" style="object-fit: cover;">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: var(--primary-gradient); color: white; font-size: 2.5rem; font-weight: 800;">
                                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Upload -->
                                    <div class="col-md-8">
                                        <p class="text-muted mb-3" style="font-size: 0.9rem;">Upload New Picture</p>
                                        <div class="border-2 border-dashed rounded p-4 text-center" style="border-color: #e2e8f0;">
                                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="d-none" onchange="previewImage(event)">
                                            <label for="profile_picture" style="cursor: pointer;">
                                                <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--primary-color);"></i>
                                                <p class="mt-3 mb-1"><strong>Click to upload</strong> or drag and drop</p>
                                                <p class="text-muted mb-0" style="font-size: 0.85rem;">PNG, JPG or GIF (Max. 2MB)</p>
                                            </label>
                                        </div>
                                        @error('profile_picture')
                                            <div class="alert alert-danger mt-2 mb-0">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Buttons -->
                                <div class="d-flex gap-2">
                                    <a href="{{ route('user.profile.show', auth()->user()->username) }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">
                                        <i class="bi bi-check-lg"></i> Save Picture
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Social Links Tab -->
                <div class="tab-pane fade" id="social-links">
                    <div class="card">
                        <div class="card-header bg-white border-bottom-0 pt-4">
                            <h5 class="mb-0">
                                <i class="bi bi-share"></i> Social Links
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.profile.update', auth()->user()->username) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Twitter -->
                                <div class="mb-4">
                                    <label for="twitter" class="form-label fw-600">
                                        <i class="bi bi-twitter"></i> Twitter
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">https://twitter.com/</span>
                                        <input type="text" id="twitter" name="twitter" value="{{ old('twitter', auth()->user()->twitter ?? '') }}" class="form-control @error('twitter') is-invalid @enderror" placeholder="twitter_handle">
                                    </div>
                                    @error('twitter')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- GitHub -->
                                <div class="mb-4">
                                    <label for="github" class="form-label fw-600">
                                        <i class="bi bi-github"></i> GitHub
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">https://github.com/</span>
                                        <input type="text" id="github" name="github" value="{{ old('github', auth()->user()->github ?? '') }}" class="form-control @error('github') is-invalid @enderror" placeholder="github_username">
                                    </div>
                                    @error('github')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- LinkedIn -->
                                <div class="mb-4">
                                    <label for="linkedin" class="form-label fw-600">
                                        <i class="bi bi-linkedin"></i> LinkedIn
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">https://linkedin.com/in/</span>
                                        <input type="text" id="linkedin" name="linkedin" value="{{ old('linkedin', auth()->user()->linkedin ?? '') }}" class="form-control @error('linkedin') is-invalid @enderror" placeholder="linkedin_username">
                                    </div>
                                    @error('linkedin')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-2 pt-3">
                                    <a href="{{ route('user.profile.show', auth()->user()->username) }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> Save Links
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card">
                        <div class="card-header bg-white border-bottom-0 pt-4">
                            <h5 class="mb-0">
                                <i class="bi bi-shield-lock"></i> Account Security
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="" class="list-group-item list-group-item-action px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Change Password</h6>
                                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Update your password regularly to keep your account secure</p>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </div>
                                </a>
                                <a href="" class="list-group-item list-group-item-action px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Two-Factor Authentication</h6>
                                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Add an extra layer of security to your account</p>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </div>
                                </a>
                                <a href="" class="list-group-item list-group-item-action px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Active Sessions</h6>
                                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Manage your active sessions and logout from other devices</p>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-link {
        color: #4a5568 !important;
        border-left: 3px solid transparent;
        padding: 10px 12px !important;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        color: var(--primary-color) !important;
        background-color: #f8f9fa;
    }

    .nav-link.active {
        color: var(--primary-color) !important;
        border-left-color: var(--primary-color) !important;
        background-color: #f8f9fa;
    }

    .border-2 {
        border-width: 2px !important;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .fw-600 {
        font-weight: 600;
    }
</style>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file && file.size <= 2 * 1024 * 1024) {
            document.getElementById('submitBtn').style.display = 'inline-block';
        } else if (file && file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            event.target.value = '';
            document.getElementById('submitBtn').style.display = 'none';
        }
    }
</script>
@endsection

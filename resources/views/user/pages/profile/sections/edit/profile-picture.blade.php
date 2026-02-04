<div class="tab-pane fade" id="profile-picture">
    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-image"></i> Profile Picture
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('user.profile.update', auth()->user()->username) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Current Avatar -->
                    <div class="col-md-4 mb-4">
                        <p class="text-muted mb-3" style="font-size: 0.9rem;">Current Picture</p>
                        <div class="rounded border d-flex align-items-center justify-content-center"
                            style="width: 120px; height: 120px; background: #f8f9fa;">
                            @if (auth()->user()->profile_photo_path)
                                <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}"
                                    alt="{{ auth()->user()->name }}" class="rounded w-100 h-100"
                                    style="object-fit: cover;">
                            @elseif(auth()->user()->profile_photo_url)
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}"
                                    class="rounded w-100 h-100" style="object-fit: cover;">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center"
                                    style="background: var(--primary-gradient); color: white; font-size: 2.5rem; font-weight: 800;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Upload -->
                    <div class="col-md-8">
                        <p class="text-muted mb-3" style="font-size: 0.9rem;">Upload New Picture</p>
                        <div class="border-2 border-dashed rounded p-4 text-center" style="border-color: #e2e8f0;">
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                                class="d-none" onchange="previewImage(event)">
                            <label for="profile_picture" style="cursor: pointer;">
                                <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--primary-color);"></i>
                                <p class="mt-3 mb-1"><strong>Click to upload</strong> or drag and drop
                                </p>
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">PNG, JPG or GIF
                                    (Max. 2MB)</p>
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
                    <a href="{{ route('user.profile.show', auth()->user()->username) }}"
                        class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">
                        <i class="bi bi-check-lg"></i> Save Picture
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

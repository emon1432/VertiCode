<div class="tab-pane fade" id="social-links">
    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-share"></i> Social Links
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('user.profile.update', auth()->user()->username) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Twitter -->
                <div class="mb-4">
                    <label for="twitter" class="form-label fw-600">
                        <i class="bi bi-twitter"></i> Twitter
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">https://twitter.com/</span>
                        <input type="text" id="twitter" name="twitter"
                            value="{{ old('twitter', auth()->user()->twitter ?? '') }}"
                            class="form-control @error('twitter') is-invalid @enderror" placeholder="twitter_handle">
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
                        <input type="text" id="github" name="github"
                            value="{{ old('github', auth()->user()->github ?? '') }}"
                            class="form-control @error('github') is-invalid @enderror" placeholder="github_username">
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
                        <input type="text" id="linkedin" name="linkedin"
                            value="{{ old('linkedin', auth()->user()->linkedin ?? '') }}"
                            class="form-control @error('linkedin') is-invalid @enderror"
                            placeholder="linkedin_username">
                    </div>
                    @error('linkedin')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 pt-3">
                    <a href="{{ route('user.profile.show', auth()->user()->username) }}"
                        class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Links
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

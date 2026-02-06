<div class="tab-pane fade" id="social-links">
    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-share"></i> Social Links
            </h5>
        </div>
        <div class="card-body">
            <form
                action="{{ route('user.profile.update', ['username' => auth()->user()->username, 'section' => 'social-links']) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <!-- Website -->
                <div class="mb-4">
                    <label for="website" class="form-label fw-600">
                        <i class="bi bi-globe"></i> Website
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">https://</span>
                        <input type="text" id="website" name="social_links[website]"
                            value="{{ old('social_links.website', auth()->user()->website ?? '') }}"
                            class="form-control @error('social_links.website') is-invalid @enderror" placeholder="yourwebsite.com">
                    </div>
                    @error('social_links.website')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Facebook -->
                <div class="mb-4">
                    <label for="facebook" class="form-label fw-600">
                        <i class="bi bi-facebook"></i> Facebook
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">https://facebook.com/</span>
                        <input type="text" id="facebook" name="social_links[facebook]"
                            value="{{ old('social_links.facebook', auth()->user()->facebook ?? '') }}"
                            class="form-control @error('social_links.facebook') is-invalid @enderror"
                            placeholder="facebook_username">
                    </div>
                    @error('social_links.facebook')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Instagram -->
                <div class="mb-4">
                    <label for="instagram" class="form-label fw-600">
                        <i class="bi bi-instagram"></i> Instagram
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">https://instagram.com/</span>
                        <input type="text" id="instagram" name="social_links[instagram]"
                            value="{{ old('social_links.instagram', auth()->user()->instagram ?? '') }}"
                            class="form-control @error('social_links.instagram') is-invalid @enderror"
                            placeholder="instagram_username">
                    </div>
                    @error('social_links.instagram')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Twitter -->
                <div class="mb-4">
                    <label for="twitter" class="form-label fw-600">
                        <i class="bi bi-twitter"></i> Twitter
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">https://twitter.com/</span>
                        <input type="text" id="twitter" name="social_links[twitter]"
                            value="{{ old('social_links.twitter', auth()->user()->twitter ?? '') }}"
                            class="form-control @error('social_links.twitter') is-invalid @enderror" placeholder="twitter_handle">
                    </div>
                    @error('social_links.twitter')
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
                        <input type="text" id="github" name="social_links[github]"
                            value="{{ old('social_links.github', auth()->user()->github ?? '') }}"
                            class="form-control @error('social_links.github') is-invalid @enderror" placeholder="github_username">
                    </div>
                    @error('social_links.github')
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
                        <input type="text" id="linkedin" name="social_links[linkedin]"
                            value="{{ old('social_links.linkedin', auth()->user()->linkedin ?? '') }}"
                            class="form-control @error('social_links.linkedin') is-invalid @enderror"
                            placeholder="linkedin_username">
                    </div>
                    @error('social_links.linkedin')
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

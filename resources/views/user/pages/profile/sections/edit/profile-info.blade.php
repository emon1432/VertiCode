<div class="tab-pane fade show active" id="profile-info">
    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-person"></i> Profile Information
            </h5>
        </div>
        <div class="card-body">
            <form
                action="{{ route('user.profile.update', ['username' => auth()->user()->username, 'section' => 'profile-info']) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3 mb-4">
                    <div class="row g-2 mb-0">
                        <div class="col-md-7">
                            <!-- Full Name -->
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label fw-600">Full Name <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="bi bi-person-fill"></i></span>
                                    <input type="text" id="name" name="name"
                                        value="{{ old('name', auth()->user()->name) }}" required
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Enter your full name">
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Username -->
                            <div class="col-md-12 mb-3">
                                <label for="username" class="form-label fw-600">Username <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">@</span>
                                    <input type="text" id="username" name="username"
                                        value="{{ old('username', auth()->user()->username) }}" required
                                        class="form-control border-start-0 @error('username') is-invalid @enderror"
                                        placeholder="your_username" readonly>
                                </div>
                                @error('username')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-5 d-flex justify-content-center align-items-center">
                            <!-- Image -->
                            <div class="card" style="width: 90%">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center p-0">
                                    <div class="position-relative mb-4 mt-4">
                                        <div class="rounded-circle overflow-hidden border-4"
                                            style="width: 160px; height: 160px; border-color: #667eea; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                            @if (auth()->user()->image && imageExists(auth()->user()->image))
                                                <img src="{{ imageShow(auth()->user()->image) }}"
                                                    alt="{{ auth()->user()->name }}" class="w-100 h-100"
                                                    style="object-fit: cover;">
                                            @elseif(auth()->user()->profile_photo_url)
                                                <img src="{{ auth()->user()->profile_photo_url }}"
                                                    alt="{{ auth()->user()->name }}" class="w-100 h-100"
                                                    style="object-fit: cover;">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center"
                                                    style="color: white; font-size: 3.5rem; font-weight: 700;">
                                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="position-absolute bottom-0 end-0">
                                            <label for="image"
                                                class="bg-white border rounded-circle p-2 text-primary"
                                                style="cursor: pointer; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);">
                                                <i class="bi bi-pencil-fill"></i>
                                                <input type="file" id="image" name="image" accept="image/*"
                                                    class="d-none" onchange="previewProfileImage(event)">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-600">Email Address <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="bi bi-envelope-fill"></i></span>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', auth()->user()->email) }}" required
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Enter your email address" readonly>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-600">Phone</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="bi bi-telephone-fill"></i></span>
                            <input type="tel" id="phone" name="phone"
                                value="{{ old('phone', auth()->user()->phone) }}"
                                class="form-control @error('phone') is-invalid @enderror"
                                placeholder="Enter your phone number">
                        </div>
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>



                    <!-- Date of Birth -->
                    <div class="col-md-4">
                        <label for="date_of_birth" class="form-label fw-600">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth"
                            value="{{ old('date_of_birth', auth()->user()->date_of_birth) }}"
                            class="form-control @error('date_of_birth') is-invalid @enderror">
                        @error('date_of_birth')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div class="col-md-4">
                        <label for="gender" class="form-label fw-600">Gender</label>
                        <select id="gender" name="gender"
                            class="form-select select2 @error('gender') is-invalid @enderror">
                            <option value="" @selected(old('gender', auth()->user()->gender) === null)>Select gender</option>
                            <option value="Male" @selected(old('gender', auth()->user()->gender) === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender', auth()->user()->gender) === 'Female')>Female
                            </option>
                            <option value="Other" @selected(old('gender', auth()->user()->gender) === 'Other')>Other
                            </option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Country -->
                    <div class="col-md-4">
                        <label for="country_id" class="form-label fw-600">Country</label>
                        <select id="country_id" name="country_id"
                            class="form-select select2-ajax @error('country_id') is-invalid @enderror"
                            data-type="countries">
                            <option value="" @selected(old('country_id', auth()->user()->country_id) === null)>
                                Select country
                            </option>
                            @if (old('country_id', auth()->user()->country_id))
                                <option value="{{ old('country_id', auth()->user()->country_id) }}" selected>
                                    {{ \App\Models\Country::find(old('country_id', auth()->user()->country_id))?->name }}
                                </option>
                            @endif
                        </select>
                        @error('country_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Institute -->
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="institute_id" class="form-label fw-600 mb-0">Institute</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#addInstituteModal">
                                <i class="bi bi-plus-circle"></i> If institute not listed
                            </button>
                        </div>
                        <select id="institute_id" name="institute_id"
                            class="form-select select2-ajax @error('institute_id') is-invalid @enderror"
                            data-type="institutes">
                            <option value="" @selected(old('institute_id', auth()->user()->institute_id) === null)>
                                Select institute
                            </option>
                            @if (old('institute_id', auth()->user()->institute_id))
                                <option value="{{ old('institute_id', auth()->user()->institute_id) }}" selected>
                                    {{ \App\Models\Institute::find(old('institute_id', auth()->user()->institute_id))?->name }}
                                </option>
                            @endif
                        </select>
                        @error('institute_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- fav_quote -->
                    <div class="col-md-12">
                        <label for="fav_quote" class="form-label fw-600">Favorite Quote</label>
                        <textarea id="fav_quote" name="fav_quote" rows="4" class="form-control @error('fav_quote') is-invalid @enderror"
                            maxlength="500" oninput="updateCharacterCount(event)" placeholder="Tell us about yourself...">{{ old('fav_quote', auth()->user()->fav_quote) }}</textarea>
                        <small class="text-muted d-block mt-2">
                            <span id="fav-quote-character-count">{{ strlen(old('fav_quote', auth()->user()->fav_quote)) }}</span>/500
                            characters
                        </small>
                        @error('fav_quote')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <a href="{{ route('user.profile.show', auth()->user()->username) }}"
                        class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Institute Modal -->
<div class="modal fade" id="addInstituteModal" tabindex="-1" aria-labelledby="addInstituteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInstituteModalLabel">
                    <i class="bi bi-building"></i> Request New Institute
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addInstituteForm">
                <div class="modal-body">
                    @csrf
                    <!-- Institute Name -->
                    <div class="mb-3">
                        <label for="institute_name" class="form-label fw-600">Institute Name <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="institute_name" name="name"
                            placeholder="Enter institute name" required>
                        <div class="invalid-feedback d-block" id="name-error"></div>
                    </div>

                    <!-- Country -->
                    <div class="mb-3">
                        <label for="institute_country" class="form-label fw-600">Country <span
                                class="text-danger">*</span></label><br>
                        <select class="form-select w-100" id="institute_country" name="country_id" required
                            data-type="countries">
                        </select>
                        <div class="invalid-feedback d-block" id="country_id-error"></div>
                    </div>

                    <!-- Website -->
                    <div class="mb-3">
                        <label for="institute_website" class="form-label fw-600">Website</label>
                        <input type="url" class="form-control" id="institute_website" name="website"
                            placeholder="https://example.com">
                        <div class="invalid-feedback d-block" id="website-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-lg"></i> Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
